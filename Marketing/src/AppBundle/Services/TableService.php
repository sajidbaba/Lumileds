<?php

namespace AppBundle\Services;

use AppBundle\Entity\Cell;
use AppBundle\Entity\User;
use AppBundle\Entity\Version;
use AppBundle\Indicators\PercentageIndicatorInterface;
use AppBundle\Model\Cell as CellModel;
use AppBundle\Model\Contribution\OneYearTable;
use AppBundle\Model\Table;
use AppBundle\Repository\CellRepository;
use AppBundle\Repository\VersionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TableService
{
    /**
     * @var CellRepository
     */
    private $cellRepo;

    /**
     * @var VersionRepository
     */
    private $versionRepo;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * TableService constructor.
     *
     * @param EntityManagerInterface $em
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage)
    {
        $this->cellRepo = $em->getRepository(Cell::class);
        $this->versionRepo = $em->getRepository(Version::class);
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Get table
     *
     * @param array $filters
     * @return Table
     */
    public function getTable(array $filters = []): Table
    {
        $filters = $this->normalizeFilters($filters);

        $table = new Table();
        $cells = $this->cellRepo->findAllFiltered($filters);

        foreach ($cells as $key => $cell) {
            $model = $this->convertCell($cell);
            $table->addCell($model);

            unset($cells[$key]);
        }

        return $table;
    }

    /**
     * Get table version
     *
     * @param int $id
     * @param array $filters
     *
     * @return Table
     */
    public function getTableVersion(int $id, array $filters = []): Table
    {
        $filters = $this->normalizeFilters($filters);

        $table = new Table();
        $result = $this->cellRepo->findVersionCells($id, $filters);

        foreach ($result as $item) {
            $model = $this->convertCell($item[0]);
            $model->setIsEditable(false);

            if ($item['version'] !== null) {
                $model->setVersionValue($item['version']);
            }

            $table->addCell($model);
            unset($item);
        }

        return $table;
    }

    /**
     * Get table
     *
     * @param array $filters
     * @return Table
     */
    public function getOneYearTable(array $filters = []): Table
    {
        $filters = $this->normalizeFilters($filters);

        $table = new OneYearTable();
        $cells = $this->cellRepo->findAllFiltered($filters);

        foreach ($cells as $key => $cell) {
            $model = $this->convertCell($cell);
            $table->addCell($model);

            unset($cells[$key]);
        }

        return $table;
    }

    /**
     * Removes empty filters
     * Filters come as arrays except for segment, which is a string
     *
     * @param array $filters
     * @return array
     */
    private function normalizeFilters($filters): array
    {
        if (is_array($filters) && count($filters) > 0) {
            foreach($filters as $key => $value) {
                if ($key == 'segment' && !empty($value)) {
                    continue;
                } elseif (!is_array($value) || empty($value)) {
                    unset($filters[$key]);
                }
            }

            if (count($filters) > 0) {
                return $filters;
            }
        }

        return [];
    }

    /**
     * Convert cell from entity to model
     *
     * @param Cell[] $cells
     *
     * @return CellModel[]
     */
    public function convertCells(array $cells = []): array
    {
        $models = [];

        foreach ($cells as $cell) {
            $models[] = $this->convertCell($cell);
        }

        return $models;
    }

    /**
     * Convert cell from entity to model
     *
     * @param Cell $cell
     *
     * @return CellModel
     */
    public function convertCell(Cell $cell): CellModel
    {
        $precision = $cell->getIndicatorClass()->getPrecision();
        $isEditable = $cell->getIndicatorClass()->isEditable($cell);
        $isPercentage = $cell->getIndicatorClass() instanceof PercentageIndicatorInterface;

        $model = (new CellModel())
            ->setId($cell->getId())
            ->setCountry($cell->getCountry()->getName())
            ->setRegion($cell->getCountry()->getRegion() ? $cell->getCountry()->getRegion()->getId() : null)
            ->setIndicator($cell->getIndicator()->getName())
            ->setIndicatorId($cell->getIndicator()->getId())
            ->setSegment($cell->getSegment()->getName())
            ->setTechnology($cell->getTechnology() ? $cell->getTechnology()->getName() : null)
            ->setTechnologyId($cell->getTechnology() ? $cell->getTechnology()->getId() : null)
            ->setValue($cell->getValue())
            ->setYear($cell->getYear())
            ->setPrecision($precision)
            ->setIsEditable($isEditable)
            ->setIsPercentage($isPercentage)
            ->setError($cell->getErrorLog() ? $cell->getErrorLog()->getMessage() : null)
            ->setErrorType($cell->getErrorLog() ? $cell->getErrorLog()->getType() : null);

        return $model;
    }

    /**
     * Check table permission on Edit page
     *
     * @param Table $table
     *
     * @return void
     */
    public function checkTablePermission(Table $table): void
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        if (!$user->hasRole(User::ROLE_ADMIN)) {
            foreach ($table->getRows() as $row) {
                foreach ($row->getCells() as $cell) {
                    $cell->setIsEditable(false);
                }
            }
        }
    }
}
