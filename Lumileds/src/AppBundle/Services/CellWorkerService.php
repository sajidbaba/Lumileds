<?php

namespace AppBundle\Services;

use AppBundle\Entity\Cell;
use AppBundle\Entity\Indicator;
use AppBundle\Entity\SheetQueue;
use AppBundle\Indicators\Output\OutputIndicatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;

class CellWorkerService
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var IndicatorService */
    private $indicatorRegistry;

    /** @var CellValidator */
    private $validator;

    /** @var VersioningService */
    private $versioningService;

    /** @var array */
    private $filter = [];

    /** @var bool */
    private $withoutSaving = false;

    /** @var Cell[] */
    private $affectedCells = [];

    /**
     * CellWorkerService constructor.
     *
     * @param EntityManagerInterface $em
     * @param IndicatorService $indicatorService
     * @param CellValidator $validator
     * @param VersioningService $versioningService
     */
    public function __construct(
        EntityManagerInterface $em,
        IndicatorService $indicatorService,
        CellValidator $validator,
        VersioningService $versioningService
    ) {
        $this->em = $em;
        $this->indicatorRegistry = $indicatorService;
        $this->validator = $validator;
        $this->versioningService = $versioningService;
    }

    public function processCells($isUpload = false)
    {
        $isInitialUploadInProgress = $this->em->getRepository(SheetQueue::class)->isInitialUploadInProgress();

        if ($isInitialUploadInProgress) {
            // is initial upload
            return;
        }

        /** @var Cell[] $cells */
        $cells = $this->yieldCell($this->getCellIterator());

        /** @var Cell|null $previousCell */
        $previousCell = null;

        foreach ($cells as $cell) {
            if ($previousCell && $cell->getYear() - $previousCell->getYear() !== 1) {
                $previousCell = null;
            }

            $oldValue = $cell->getValue();

            /** @var OutputIndicatorInterface $indicatorClass */
            $indicatorClass = $cell->getIndicatorClass();
            $calculatedValue = $indicatorClass->getCalculatedValue(
                $cell,
                $this->em,
                $isUpload
            );
            $cell->setValue($calculatedValue);

            $this->validator->validate($cell, $previousCell);
            $this->addVersionCell($cell, $oldValue);
            $this->addAffectedCell($cell, $oldValue);

            $previousCell = $cell;
        }

        $this->saveChanges();
    }

    /**
     * Save calculations to database
     */
    private function saveChanges(): void
    {
        if (!$this->withoutSaving) {
            $this->validator->persistQueuedErrors();
            $this->em->flush();
        }
    }

    /**
     * Fetches an iterator within cell entities.
     *
     * @return IterableResult
     */
    private function getCellIterator()
    {
        $query = $this->em->createQuery($this->getDQL());

        return $query->iterate();
    }

    /**
     * Get DQL based on filters
     *
     * @return string
     */
    private function getDQL(): string
    {
        $dql = 'SELECT c,e FROM '.Cell::class.' c INNER JOIN c.indicator i LEFT JOIN c.errorLog e ';
        $dql .= 'WHERE c.indicator = i AND i.type IN('.Indicator::INDICATOR_TYPE_OUTPUT.','.Indicator::INDICATOR_TYPE_MIXED.')';

        if (array_key_exists('countryId', $this->filter)) {
            $dql .= ' AND c.country = '.$this->filter['countryId'];
        }

        if (array_key_exists('segmentId', $this->filter)) {
            $dql .= ' AND c.segment = '.$this->filter['segmentId'];
        }

        if (array_key_exists('year', $this->filter)) {
            $dql .= ' AND c.year = '.$this->filter['year'];
        }

        if (array_key_exists('indicatorIds', $this->filter)) {
            $ids = implode(',', $this->filter['indicatorIds']);
            $dql .= ' AND i.id IN ('.$ids.')';
        }

        if (array_key_exists('technologyIds', $this->filter)) {
            $ids = implode(',', $this->filter['technologyIds']);
            $dql .= ' AND c.technology IN ('.$ids.')';
        }

        $dql .= ' ORDER BY c.segment, c.indicator, c.technology, c.year';

        return $dql;
    }

    /**
     * Yields cell result.
     *
     * @param IterableResult $iterator
     *
     * @return \Generator
     */
    private function yieldCell(IterableResult $iterator)
    {
        foreach ($iterator as $object) {
            yield current($object);
        }
    }

    /**
     * Set filters for query
     *
     * @param array $filter
     *
     * @return CellWorkerService
     */
    public function setFilter(array $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Set not to save calculated cells
     *
     * @param bool $withoutSaving
     *
     * @return CellWorkerService
     */
    public function setWithoutSaving(bool $withoutSaving): self
    {
        $this->withoutSaving = $withoutSaving;

        return $this;
    }

    /**
     * Get cells that where recalculated
     *
     * @return Cell[]
     */
    public function getAffectedCells(): array
    {
        return $this->affectedCells;
    }

    /**
     * Store cells that where recalculated
     *
     * @param Cell $cell
     * @param null|string $oldValue
     *
     * @return CellWorkerService
     */
    private function addAffectedCell(Cell $cell, ?string $oldValue): self
    {
        if ($this->withoutSaving && $cell->getValue() != $oldValue) {
            $this->affectedCells[$cell->getId()] = $cell;
        }

        return $this;
    }

    /**
     * Store version cell
     *
     * @param Cell $cell
     * @param string|null $oldValue
     *
     * @return CellWorkerService
     */
    private function addVersionCell(Cell $cell, ?string $oldValue): self
    {
        if (!$this->withoutSaving) {
            $this->versioningService->trackCell($cell->getId(), $oldValue, $cell->getValue());
        }

        return $this;
    }
}
