<?php

namespace AppBundle\Services;

use AppBundle\Entity\Cell;
use AppBundle\Entity\Contribution;
use AppBundle\Entity\ContributionApprove;
use AppBundle\Entity\ContributionApproveRow;
use AppBundle\Entity\ContributionCellModification;
use AppBundle\Entity\ContributionCountryRequest;
use AppBundle\Entity\ContributionIndicatorRequest;
use AppBundle\Entity\ContributionRequest;
use AppBundle\Entity\Country;
use AppBundle\Entity\Indicator;
use AppBundle\Entity\Region;
use AppBundle\Entity\Segment;
use AppBundle\Entity\Technology;
use AppBundle\Entity\User;
use AppBundle\Model\Cell as CellModel;
use AppBundle\Model\Table;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContributionManager
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var TableService */
    private $tableService;

    /** @var VersioningService */
    private $versioningService;

    /** @var ContributionIndicatorService  */
    private $contributionIndicatorService;

    /**
     * @param EntityManagerInterface $em
     * @param TableService $tableService
     * @param VersioningService $versioningService
     * @param ContributionIndicatorService $contributionIndicatorService
     */
    public function __construct(
        EntityManagerInterface $em,
        TableService $tableService,
        VersioningService $versioningService,
        ContributionIndicatorService $contributionIndicatorService
    ) {
        $this->em = $em;
        $this->tableService = $tableService;
        $this->versioningService = $versioningService;
        $this->contributionIndicatorService = $contributionIndicatorService;
    }

    /**
     * @param Region $region
     * @param string $deadline
     *
     * @return ContributionRequest
     */
    public function requestContribution(Region $region, string $deadline): ContributionRequest
    {
        $contributionRequest = $region->getContributionRequest();

        if (!$contributionRequest) {
            $contributionRequest = new ContributionRequest();
            $contributionRequest->setRegion($region);

            foreach ($region->getCountries() as $country) {
                $contributionCountryRequest = new ContributionCountryRequest();
                $contributionCountryRequest->setContributionRequest($contributionRequest);
                $contributionCountryRequest->setCountry($country);

                $this->em->persist($contributionCountryRequest);

                foreach (ContributionIndicatorRequest::INDICATOR_GROUPS as $indicatorGroup) {
                    $contributionIndicatorRequest = new ContributionIndicatorRequest();
                    $contributionIndicatorRequest->setIndicatorGroup($indicatorGroup);
                    $contributionIndicatorRequest->setContributionCountryRequest($contributionCountryRequest);

                    $this->em->persist($contributionIndicatorRequest);
                }
            }

            $this->em->persist($contributionRequest);
        } else {
            foreach ($contributionRequest->getContributionCountryRequests() as $contributionCountryRequest) {
                // clear cell modification and row approved if status is changed from finished to requested
                if ($contributionCountryRequest->getStatus() === ContributionCountryRequest::STATUS_APPROVED) {
                    foreach ($contributionCountryRequest->getContributionApproveRows() as $contributionApproveRow) {
                        $this->em->remove($contributionApproveRow);
                    }

                    foreach ($contributionCountryRequest->getContributions() as $contribution) {
                        foreach ($contribution->getContributionCellModifications() as $cellModification) {
                            $this->em->remove($cellModification);
                        }
                    }
                }

                $contributionCountryRequest->setStatus(ContributionCountryRequest::STATUS_REQUIRED);

                foreach ($contributionCountryRequest->getContributionApproves() as $contributionApprove) {
                    $this->em->remove($contributionApprove);
                }
            }
        }

        $contributionRequest->setDeadline(new \DateTime($deadline));

        $this->em->flush();

        return $contributionRequest;
    }

    /**
     * @param User $user
     * @param ContributionCountryRequest $contributionCountryRequest
     * @param string $segment
     *
     * @return Table
     */
    public function getAdminTable(User $user, ContributionCountryRequest $contributionCountryRequest, string $segment): Table
    {
        $table = $this->tableService->getTable([
            'markets' => [$contributionCountryRequest->getCountry()],
            'segment' => $segment,
        ]);

        $this->applyContributionModifications($user, $table, $contributionCountryRequest);
        $table->setApproved($this->isSegmentApproved($contributionCountryRequest, $segment));

        foreach ($table->getRows() as $row) {
            foreach ($row->getCells() as $cell) {
                if ($cell->isEditable()) {
                    $cell->setIsEditable(!$table->isApproved());
                }
            }

            $approves = $this->getContributionApprove(
                $contributionCountryRequest,
                $row->getMarket(),
                $row->getIndicator(),
                $row->getSegment(),
                $row->getTechnology()
            );

            $row->setApproved((bool)$approves->count());

            $row->setIsContributedByContributor(count(array_filter($row->getCells(), function(CellModel $cell) {
                return $cell->isContributedByContributor();
            })));
        }

        return $table;
    }


    /**
     * @param User $user
     * @param ContributionIndicatorRequest $contributionIndicatorRequest
     * @param string $segment
     *
     * @return Table[]
     */
    public function getContributorTables(User $user, ContributionIndicatorRequest $contributionIndicatorRequest, string $segment): array
    {
        $currentYear = date('Y');

        $this->contributionIndicatorService->setIndicatorGroup($contributionIndicatorRequest->getIndicatorGroup());
        $contributionCountryRequest = $contributionIndicatorRequest->getContributionCountryRequest();
        $country = $contributionCountryRequest->getCountry()->getId();

        $filters = [
            'markets' => [$country],
            'segment' => $segment,
        ];

        $years = range($currentYear - 1, $currentYear + 4);

        $filters1 = array_merge(
            $filters,
            [
                'years' => $years,
                'indicators' => $this->contributionIndicatorService->getIndicatorsTable1(),
                'technologies' => $this->contributionIndicatorService->getTechnologiesTable1()
            ]
        );
        $table1 = $this->tableService->getTable($filters1);
        $table1->setTitle($this->contributionIndicatorService->getTitleTable1());
        $table1->setInstruction($this->contributionIndicatorService->getInstructions());

        $filters2 = array_merge(
            $filters,
            [
                'years' => $years,
                'indicators' => $this->contributionIndicatorService->getIndicatorsTable2(),
                'technologies' => $this->contributionIndicatorService->getTechnologiesTable2()
            ]
        );
        $table2 = $this->tableService->getTable($filters2);
        $table2->setTitle($this->contributionIndicatorService->getTitleTable2());
        $table2->setInstruction($this->contributionIndicatorService->getInstructions());

        $tables = [$table1, $table2];

        foreach ($tables as $table) {
            $this->applyContributionModifications($user, $table, $contributionCountryRequest);
            $this->applyEditableCells($table, $contributionIndicatorRequest);
            $this->changeLabels($table);
        }

        return $tables;
    }

    /**
     * Apply contribution modifications to table
     *
     * @param User $user
     * @param Table $table
     * @param ContributionCountryRequest $contributionCountryRequest
     */
    private function applyContributionModifications(User $user, Table $table, ContributionCountryRequest $contributionCountryRequest)
    {
        if ($user->isAdmin() && $contributionCountryRequest->getStatus() !== ContributionCountryRequest::STATUS_SUBMITTED) {
            return;
        }

        foreach ($table->getRows() as $row) {
            foreach ($row->getCells() as $cell) {
                foreach ($contributionCountryRequest->getContributions() as $contribution) {
                    foreach ($contribution->getContributionCellModifications() as $cellModification) {
                        if ($cellModification->getCell()->getId() === $cell->getId()) {
                            $cell->setValue($cellModification->getValue());
                            $cell->setContributed(true);

                            if ($cellModification->getUser()->isContributor()) {
                                $cell->setContributedByContributor(true);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Change needed cells to not editable
     *
     * @param Table $table
     */
    private function applyEditableCells(Table $table): void
    {
        $previousYear = date('Y') - 1;

        foreach ($table->getRows() as $row) {
            foreach ($row->getCells() as $cell) {
                if ($cell->isEditable()) {
                    $isApproved = !$table->isApproved();
                    $isEditable = $isApproved && $cell->getYear() != $previousYear;

                    $cell->setIsEditable($isEditable);
                }
            }
        }
    }

    /**
     * @param User $user
     * @param ContributionCountryRequest $contributionCountryRequest
     * @param string|null $comment
     *
     * @return void
     */
    public function saveContributorComment(
        User $user,
        ContributionCountryRequest $contributionCountryRequest,
        ?string $comment
    ): void {
        $contribution = new Contribution();
        $contribution->setComment($comment);
        $contribution->setContributionCountryRequest($contributionCountryRequest);
        $contribution->setUser($user);

        $this->em->persist($contribution);
        $this->em->flush();
    }

    /**
     * @param User $user
     * @param ContributionCountryRequest $contributionCountryRequest
     *
     * @return void
     */
    public function saveContributorFeedback(
        User $user,
        ContributionCountryRequest $contributionCountryRequest
    ): void {
        $contributionCountryRequest->setStatus(ContributionCountryRequest::STATUS_SUBMITTED);
        foreach ($contributionCountryRequest->getContributionIndicatorRequests() as $contributionIndicatorRequest) {
            $contributionIndicatorRequest->setStatus(ContributionIndicatorRequest::STATUS_SUBMITTED);
        }

        $contribution = new Contribution();
        $contribution->setContributionCountryRequest($contributionCountryRequest);
        $contribution->setUser($user);

        $this->em->persist($contribution);
        $this->em->flush();
    }

    /**
     * @param User $user
     * @param ContributionIndicatorRequest $contributionIndicatorRequest
     * @param array $changedCells
     * @param string $segment
     *
     * @return void
     */
    public function saveContributorIndicatorFeedback(
        User $user,
        ContributionIndicatorRequest $contributionIndicatorRequest,
        array $changedCells,
        string $segment
    ): void {
        $contributionIndicatorRequest->setStatus(ContributionIndicatorRequest::STATUS_REVIEWED);
        $contributionCountryRequest = $contributionIndicatorRequest->getContributionCountryRequest();

        $contribution = new Contribution();
        $contribution->setContributionCountryRequest($contributionCountryRequest);
        $contribution->setUser($user);

        if ($changedCells) {
            $this->applyChangedCells($changedCells, $user, $contribution);
            $this->removeSegmentApprove($contributionCountryRequest, $segment);
        }

        $this->em->persist($contribution);
        $this->em->flush();
    }

    /**
     * @param User $user
     * @param ContributionCountryRequest $contributionCountryRequest
     * @param string|null $comment
     * @param string $segmentName
     * @param array|null $reviewedRows
     * @param array|null $changedCells
     * @param string $approveType
     *
     * @return void
     */
    public function saveAdminFeedback(
        User $user,
        ContributionCountryRequest $contributionCountryRequest,
        ?string $comment,
        string $segmentName,
        ?array $reviewedRows,
        ?array $changedCells,
        string $approveType
    ): void {
        $contribution = new Contribution();
        $contribution->setContributionCountryRequest($contributionCountryRequest);
        $contribution->setUser($user);

        $approveSegment = $approveType === 'approveSegment';

        if ($comment) {
            $contribution->setComment($comment);
        }

        $this->em->persist($contribution);

        if ($approveSegment && !$this->isSegmentApproved($contributionCountryRequest, $segmentName)) {
            // Create approve
            $segment = $this->em->getRepository(Segment::class)->findOneBy(['name' => $segmentName]);

            $contributionApprove = new ContributionApprove();
            $contributionApprove->setUser($user);
            $contributionApprove->setContributionCountryRequest($contributionCountryRequest);
            $contributionApprove->setSegment($segment);
            $contributionCountryRequest->addContributionApprove($contributionApprove);

            $this->em->persist($contributionApprove);

            // Move cells modification to cells table
            foreach ($contributionCountryRequest->getContributions() as $contribution) {
                foreach ($contribution->getContributionCellModifications() as $cellModification) {
                    $cell = $cellModification->getCell();

                    if ($cell->getSegment()->getName() === $segmentName) {
                        $this->versioningService->trackCell($cell->getId(), $cell->getValue(), $cellModification->getValue());

                        $cell->setValue($cellModification->getValue());
                    }
                }
            }

            // Change status to contribution request if all segments are approved
            if ($contributionCountryRequest->getContributionApproves()->count() === count(Segment::SEGMENTS)) {
                $contributionCountryRequest->setStatus(ContributionCountryRequest::STATUS_APPROVED);

                foreach ($contributionCountryRequest->getContributionIndicatorRequests() as $contributionIndicatorRequest) {
                    $contributionIndicatorRequest->setStatus(ContributionIndicatorRequest::STATUS_APPROVED);
                }
            }
        }

        if (!$approveSegment && $this->isSegmentApproved($contributionCountryRequest, $segmentName)) {
            $this->removeSegmentApprove($contributionCountryRequest, $segmentName);

            if ($contributionCountryRequest->getStatus() === ContributionCountryRequest::STATUS_APPROVED) {
                $contributionCountryRequest->setStatus(ContributionCountryRequest::STATUS_REQUIRED);
            }
        }

        if ($reviewedRows) {
            $indicatorRepository = $this->em->getRepository(Indicator::class);
            $countryRepository = $this->em->getRepository(Country::class);
            $segmentRepository = $this->em->getRepository(Segment::class);
            $technologyRepository = $this->em->getRepository(Technology::class);

            foreach ($reviewedRows as $reviewedRow) {
                $indicator = $indicatorRepository->findOneBy(['name' => $reviewedRow['indicator']]);
                $country = $countryRepository->findOneBy(['name' => $reviewedRow['market']]);
                $segment = $segmentRepository->findOneBy(['name' => $reviewedRow['segment']]);

                if (isset($reviewedRow['technology'])) {
                    $technology = $technologyRepository->findOneBy(['name' => $reviewedRow['technology']]);
                } else {
                    $technology = null;
                }

                if ($reviewedRow['state'] === true) {
                    $contributionApprove = new ContributionApproveRow();
                    $contributionApprove->setUser($user);
                    $contributionApprove->setContributionCountryRequest($contributionCountryRequest);
                    $contributionApprove->setIndicator($indicator);
                    $contributionApprove->setCountry($country);
                    $contributionApprove->setSegment($segment);
                    $contributionApprove->setTechnology($technology);

                    $this->em->persist($contributionApprove);
                } else {
                    $approves = $this->getContributionApprove(
                        $contributionCountryRequest,
                        $country,
                        $indicator,
                        $segment,
                        $technology
                    );

                    foreach ($approves as $approve) {
                        $this->em->remove($approve);
                    }
                }
            }
        }

        if ($changedCells) {
            $this->applyChangedCells($changedCells, $user, $contribution);

            $this->removeSegmentApprove($contributionCountryRequest, $segmentName);
        }

        if ($this->versioningService->hasTrackedCells()) {
            $this->versioningService->initVersion($user);
            $this->versioningService->createVersion();
        }

        $this->em->flush();
    }

    /**
     * @param array|null $changedCells
     * @param User $user
     * @param Contribution $contribution
     *
     * @return void
     */
    private function applyChangedCells(?array $changedCells, User $user, Contribution $contribution): void
    {
        if (!$changedCells) {
            return;
        }

        $cells = $this->em->getRepository(Cell::class)->findByIdsWithContributions(array_filter($changedCells, function(array $changedCell) {
            return $changedCell['id'];
        }));
        $cells = new ArrayCollection($cells);

        foreach ($changedCells as $changedCell) {
            $cell = $cells->filter(function(Cell $cell) use ($changedCell) {
                return $cell->getId() == $changedCell['id'];
            })->first();

            if (!$cell) {
                throw new NotFoundHttpException(sprintf('Can\'t find Cell with id %d', $changedCell['id']));
            }

            if ($previousModification = $cell->getContributionCellModifications()->first()) {
                $cell->removeContributionCellModification($previousModification);
                $this->em->remove($previousModification);
            }

            $contributionCellModification = new ContributionCellModification();
            $contributionCellModification->setUser($user);
            $contributionCellModification->setValue($changedCell['value']);
            $contributionCellModification->setCell($cell);
            $contributionCellModification->setContribution($contribution);

            $this->em->persist($contributionCellModification);
        }
    }

    /**
     * @param ContributionCountryRequest $contributionCountryRequest
     * @param string $segment
     *
     * @return bool
     */
    private function isSegmentApproved(ContributionCountryRequest $contributionCountryRequest, string $segment): bool
    {
        return (bool)$contributionCountryRequest->getContributionApproves()
            ->filter(function(ContributionApprove $contributionApprove) use ($segment) {
                return $contributionApprove->getSegment()->getName() === $segment;
            })->count();
    }

    /**
     * @param ContributionCountryRequest $contributionCountryRequest
     * @param string $segment
     *
     * @return void
     */
    private function removeSegmentApprove(ContributionCountryRequest $contributionCountryRequest, string $segment): void
    {
        $contributionApprove = $contributionCountryRequest->getContributionApproves()
            ->filter(function(ContributionApprove $contributionApprove) use ($segment) {
                return $contributionApprove->getSegment()->getName() === $segment;
            })->first();

        if ($contributionApprove) {
            $this->em->remove($contributionApprove);
        }
    }

    /**
     * @param ContributionCountryRequest $contributionCountryRequest
     * @param string $marketName
     * @param string $indicatorName
     * @param string $segmentName
     * @param null|string $technologyName
     *
     * @return ArrayCollection
     */
    private function getContributionApprove(
        ContributionCountryRequest $contributionCountryRequest,
        string $marketName,
        string $indicatorName,
        string $segmentName,
        ?string $technologyName
    ) : ArrayCollection {
        return $contributionCountryRequest->getContributionApproveRows()
            ->filter(function(ContributionApproveRow $contributionApproveRow) use ($marketName, $indicatorName, $segmentName, $technologyName) {
                return $contributionApproveRow->getCountry()->getName() === $marketName &&
                    $contributionApproveRow->getIndicator()->getName() === $indicatorName &&
                    $contributionApproveRow->getSegment()->getName() === $segmentName &&
                    (
                        ($contributionApproveRow->getTechnology() === null && $technologyName === null) ||
                        ($contributionApproveRow->getTechnology() !== null && $contributionApproveRow->getTechnology()->getName() === $technologyName)
                    );
            });
    }

    /**
     * Change label for different indicator groups
     *
     * @param Table $table
     *
     * @return Table
     */
    private function changeLabels(Table $table): Table
    {
        return $this->contributionIndicatorService->changeLabels($table);
    }
}
