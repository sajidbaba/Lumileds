<?php

namespace AppBundle\Services;

use AppBundle\Entity\Cell;
use AppBundle\Entity\Indicator;
use AppBundle\Entity\SavedFilter;
use AppBundle\Entity\Setting;
use AppBundle\Entity\SheetQueue;
use AppBundle\Entity\Country;
use AppBundle\Entity\Region;
use AppBundle\Entity\Technology;
use AppBundle\Entity\User;
use AppBundle\Model\Cell as CellModel;
use AppBundle\Indicators\IndicatorInterface;
use AppBundle\Indicators\Input\InputIndicatorInterface;
use AppBundle\Model\Table;
use AppBundle\Repository\CellRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ApiManager
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var CellRepository
     */
    private $repo;

    /**
     * @var CellWorkerService
     */
    private $cellWorker;

    /**
     * @var CellValidator
     */
    private $validator;

    /**
     * @var TableService
     */
    private $tableService;

    /**
     * @var RegionService
     */
    private $regionService;

    /**
     * @var CountryService
     */
    private $countryService;

    /**
     * @var IndicatorService
     */
    private $indicatorService;

    /**
     * @var VersioningService
     */
    private $versioningService;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * ApiManager constructor.
     *
     * @param EntityManagerInterface $em
     * @param CellWorkerService $cellWorker
     * @param CellValidator $validator
     * @param TableService $tableService
     * @param RegionService $regionService
     * @param CountryService $countryService
     * @param IndicatorService $indicatorService
     * @param VersioningService $versioningService
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        EntityManagerInterface $em,
        CellWorkerService $cellWorker,
        CellValidator $validator,
        TableService $tableService,
        RegionService $regionService,
        CountryService $countryService,
        IndicatorService $indicatorService,
        VersioningService $versioningService,
        TokenStorageInterface $tokenStorage
    ) {
        $this->em = $em;
        $this->repo = $em->getRepository(Cell::class);
        $this->cellWorker = $cellWorker;
        $this->validator = $validator;
        $this->tableService = $tableService;
        $this->regionService = $regionService;
        $this->countryService = $countryService;
        $this->indicatorService = $indicatorService;
        $this->versioningService = $versioningService;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Get table
     *
     * @param array $filters
     *
     * @return Table
     */
    public function getTable(array $filters): Table
    {
        $this->saveFilters($this->tokenStorage->getToken()->getUser(), $filters);

        $table = $this->tableService->getTable($filters);
        $this->tableService->checkTablePermission($table);

        return $table;
    }

    /**
     * @return Region[]
     */
    public function getRegions(): array
    {
        return $this->regionService->getRegions();
    }

    /**
     * @return Country[]
     */
    public function getCountries(): array
    {
        return $this->countryService->getCountries();
    }

    /**
     * @return Indicator[]
     */
    public function getIndicators(): array
    {
        return $this->indicatorService->getRegistry();
    }

    /**
     * @return Technology[]
     */
    public function getTechnologies(): array
    {
        return $this->em->getRepository(Technology::class)
            ->createQueryBuilder('t')
            ->select('t.id, t.name')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getYears(User $user): array
    {
        $years = $this->em->getRepository(Cell::class)
            ->createQueryBuilder('c')
            ->select('c.year')
            ->distinct(true)
            ->getQuery()
            ->getResult();

        if ($user->isAdmin()) {
            return $years;
        }

        $userReportingFromYear = $this->em->getRepository(Setting::class)->findOneBy(['key' => Setting::USER_REPORTING_FROM_YEAR]);
        $userReportingToYear = $this->em->getRepository(Setting::class)->findOneBy(['key' => Setting::USER_REPORTING_TO_YEAR]);

        $allowedYears = range($userReportingFromYear->getValue(), $userReportingToYear->getValue());

        foreach ($years as $key => $year) {
            if (!in_array($year['year'], $allowedYears)) {
                unset($years[$key]);
            }
        }

        return array_values($years);
    }

    /**
     * Get versioned table
     *
     * @param int $id
     * @param array $filters
     *
     * @return Table
     */
    public function getTableVersion(int $id, array $filters): Table
    {
        return $this->tableService->getTableVersion($id, $filters);
    }

    /**
     * Calculate cells affected by cell
     *
     * @param CellModel[] $cellModels
     *
     * @return CellModel[]
     */
    public function calculate(array $cellModels = []): array
    {
        $affectedCells = [];

        $cells = $this->em->getRepository(Cell::class)->findBy(['id' => array_map(function(CellModel $cellModel) {
            return $cellModel->getId();
        }, $cellModels)]);
        $cells = new ArrayCollection($cells);

        foreach ($cellModels as $cellModel) {
            /** @var Cell $entity */
            $entity = $cells->filter(function(Cell $cell) use ($cellModel) {
                return $cell->getId() == $cellModel->getId();
            })->first();

            if (!$entity) {
                throw new NotFoundHttpException();
            }

            /** @var InputIndicatorInterface|IndicatorInterface $indicator */
            $indicator = $entity->getIndicatorClass();

            if (!is_numeric($cellModel->getValue()) || !$indicator instanceof InputIndicatorInterface) {
                continue;
            }

            $entity->setValue($cellModel->getValue());
        }

        foreach ($cellModels as $cellModel) {
            /** @var Cell $entity */
            $entity = $cells->filter(function(Cell $cell) use ($cellModel) {
                return $cell->getId() == $cellModel->getId();
            })->first();

            /** @var InputIndicatorInterface|IndicatorInterface $indicator */
            $indicator = $entity->getIndicatorClass();

            if (!is_numeric($cellModel->getValue()) || !$indicator instanceof InputIndicatorInterface) {
                continue;
            }

            $affectedCells[$entity->getId()] = $entity;

            $nextCell = $this->repo->findAdjoiningCell($entity, 1);
            if ($nextCell && !array_key_exists($nextCell->getId(), $affectedCells)) {
                $affectedCells[$nextCell->getId()] = $nextCell;
            }

            $filters = $this->createFilters($entity);

            $this->cellWorker->setFilter($filters);
            $this->cellWorker->setWithoutSaving(true);
            $this->cellWorker->processCells();

            $affectedCells += $this->cellWorker->getAffectedCells();
        }

        foreach ($affectedCells as $entity) {
            /** @var Cell $previousCell */
            $previousCell = $this->repo->findAdjoiningCell($entity, -1);
            /** @var Cell $nextCell */
            $nextCell = $this->repo->findAdjoiningCell($entity, 1);

            $this->validator->validate($entity, $previousCell);
            $this->validator->validateNextCell($nextCell, $entity);
        }

        $this->validator->persistQueuedErrors();

        return $this->tableService->convertCells($affectedCells);
    }

    /**
     * Save model from input cells
     *
     * @param CellModel[] $cellArray
     * @param string|null $versionName
     *
     * @return void
     *
     * @throws \Exception
     */
    public function saveCells(array $cellArray, string $versionName = null): void
    {
        foreach ($cellArray as $cell) {
            /** @var Cell $entity */
            $entity = $this->repo->find($cell->getId());

            $this->versioningService->trackCell($cell->getId(), $entity->getValue(), $cell->getValue());

            $previousCell = $this->repo->findAdjoiningCell($entity, -1);
            $nextCell = $this->repo->findAdjoiningCell($entity, 1);

            $entity->setValue($cell->getValue());
            $this->validator->validate($entity, $previousCell);
            $this->validator->validateNextCell($nextCell, $entity);

            if (!$entity->getIndicatorClass() instanceof InputIndicatorInterface) {
                continue;
            }

            $filters = $this->createFilters($entity);
            $this->cellWorker->setFilter($filters);
            $this->cellWorker->processCells();
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $this->validator->persistQueuedErrors();
        $this->versioningService->initVersion($user, $versionName);
        $this->versioningService->createVersion();
    }

    /**
     * @param string $hash
     *
     * @return float|null
     */
    public function getUploadStatus(?string $hash) : ?float
    {
        if (!$hash) return null;
        $chunks = $this->em->getRepository(SheetQueue::class)->findBy(['hash' => $hash]);

        if (($count = count($chunks)) == 0) return null;

        $processed = 0;
        foreach ($chunks as $chunk) {
            if ($chunk->getProcessed()) {
                $processed++;
            }
        }

        return round(100 / $count * $processed, 2);
    }

    /**
     * Create filters for processing cells
     *
     * @param Cell $entity
     *
     * @return array
     */
    private function createFilters(Cell $entity): array
    {
        /** @var InputIndicatorInterface $indicator */
        $indicator = $entity->getIndicatorClass();

        $filters = [
            'countryId' => $entity->getCountry()->getId(),
            'segmentId' => $entity->getSegment()->getId(),
//            'year' => $entity->getYear(),
        ];

        if ($indicator->getAffectedIndicators()) {
            $filters['indicatorIds'] = $indicator->getAffectedIndicators();
        }

        return $filters;
    }

    /**
     * @param User $user
     * @param array $filters
     */
    private function saveFilters(User $user, array $filters): void
    {
        if (!$filters) return;

        $savedFilter = $this->em->getRepository(SavedFilter::class)->findOrCreateByUser($user);

        $savedFilter->setEditFilter($filters);

        $this->em->flush();
    }
}
