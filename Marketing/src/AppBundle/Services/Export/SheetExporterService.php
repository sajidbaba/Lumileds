<?php

namespace AppBundle\Services\Export;

use AppBundle\Services\TableService;
use Doctrine\ORM\EntityManagerInterface;
use Liuggio\ExcelBundle\Factory as ExcelFactory;
use AppBundle\Model\Cell as CellModel;
use AppBundle\Model\Table;
use PHPExcel_Cell;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SheetExporterService extends AbstractExporterService
{
    const HEADERS_ROW = 1;
    const YEARS_ROW = 1;
    const CELLS_START_ROW = 2;
    const CELLS_START_COLUMN = 0;

    const MARKET_ROW = 0;
    const SEGMENT_ROW = 1;
    const INDICATOR_ROW = 2;
    const TECHNOLOGY_ROW = 3;

    /** @var TableService */
    private $tableService;

    /** @var int|null */
    private $version = null;

    /** @var array */
    private $headers = [
        'LL Market',
        'Segment',
        'Indicator',
        'Technology',
    ];

    /**
     * @param ExcelFactory $excelService
     * @param EntityManagerInterface $em
     * @param TokenStorageInterface $tokenStorage
     * @param TableService $tableService
     */
    public function __construct(
        ExcelFactory $excelService,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        TableService $tableService
    ) {
        parent::__construct($excelService, $em, $tokenStorage);

        $this->tableService = $tableService;
    }

    /**
     * Set version
     *
     * @param int $version
     */
    public function setVersion(int $version): void
    {
        $this->version = $version;
    }

    /**
     * Fill cell values with data from database
     */
    protected function fillValues(): void
    {
        ini_set('memory_limit', -1);
        set_time_limit(0);

        $table = $this->getTable();

        $this->fillHeaders();
        $this->fillYears($table);
        $this->fillCells($table);
    }

    /**
     * Get table
     *
     * @return Table
     */
    private function getTable(): Table
    {
        if ($this->version) {
            $table = $this->tableService->getTableVersion($this->version, $this->filters);
        } else {
            $table = $this->tableService->getTable($this->filters);
        }

        return $table;
    }

    /**
     * Fill headers (LL Market, Segment, Indicator, Technology)
     */
    private function fillHeaders(): void
    {
        $sheet = $this->excelObject->getActiveSheet();

        foreach ($this->headers as $columnPosition => $header) {
            $sheet->setCellValueByColumnAndRow($columnPosition, self::HEADERS_ROW, $header);
        }
    }

    /**
     * Fill years (2015, 2016, 2017, 2018, etc.)
     *
     * @param Table $table
     */
    private function fillYears(Table $table): void
    {
        $sheet = $this->excelObject->getActiveSheet();

        $leftOffset = $this->getLeftOffset();
        foreach ($table->getYears() as $columnPosition => $year) {
            $sheet->setCellValueByColumnAndRow($leftOffset + $columnPosition, self::YEARS_ROW, $year);
        }
    }

    /**
     * Fill cells from database
     *
     * @param Table $table
     */
    private function fillCells(Table $table): void
    {
        $sheet = $this->excelObject->getActiveSheet();

        $leftOffset = $this->getLeftOffset();
        $columnPosition = self::CELLS_START_COLUMN;
        $rowPosition = self::CELLS_START_ROW;

        foreach ($table->getRows() as $row) {
            $sheet->setCellValueByColumnAndRow(self::MARKET_ROW, $rowPosition, $row->getMarket());
            $sheet->setCellValueByColumnAndRow(self::SEGMENT_ROW, $rowPosition, $row->getSegment());
            $sheet->setCellValueByColumnAndRow(self::INDICATOR_ROW, $rowPosition, $row->getIndicator());
            $sheet->setCellValueByColumnAndRow(self::TECHNOLOGY_ROW, $rowPosition, $row->getTechnology());

            foreach ($row->getCells() as $cell) {
                $excelCell = $sheet->setCellValueByColumnAndRow(
                    $leftOffset + $columnPosition,
                    $rowPosition,
                    $cell->getValue(),
                    true
                );

                $this->setNumberFormat($cell, $excelCell);

                $columnPosition++;
            }

            $columnPosition = self::CELLS_START_COLUMN;
            $rowPosition++;
        }
    }

    /**
     * @param CellModel $cell
     * @param PHPExcel_Cell $excelCell
     */
    private function setNumberFormat(CellModel $cell, PHPExcel_Cell $excelCell): void
    {
        $numberFormat = $excelCell->getStyle()->getNumberFormat();

        if ($cell->isPercentage()) {
            $format = '0';
            $format .= $this->formatPrecision($cell);
            $format .= '%';
        } else {
            $format = '#,##0';
            $format .= $this->formatPrecision($cell);
        }

        $numberFormat->setFormatCode($format);
    }

    /**
     * Generate precision format
     *
     * @param CellModel $cell
     * @return string
     */
    private function formatPrecision(CellModel $cell): string
    {
        if ($cell->getPrecision() > 0) {
            return '.'.str_repeat('0', $cell->getPrecision());
        }

        return '';
    }

    /**
     * Get left offset
     *
     * @return int
     */
    private function getLeftOffset(): int
    {
        return count($this->headers);
    }
}
