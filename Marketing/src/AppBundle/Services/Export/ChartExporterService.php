<?php

namespace AppBundle\Services\Export;

use AppBundle\Model\Reporting\Table;
use AppBundle\Services\ChartTableService;
use Doctrine\ORM\EntityManagerInterface;
use Liuggio\ExcelBundle\Factory as ExcelFactory;
use PHPExcel_Cell;
use PHPExcel_Style_NumberFormat;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ChartExporterService extends AbstractExporterService
{
    const HORIZONTAL_LABEL_ROW = 1;
    const HORIZONTAL_LABEL_OFFSET = 1;
    const VERTICAL_LABEL_COLUMN = 0;
    const VERTICAL_LABEL_OFFSET = 2;

    /** @var ChartTableService */
    private $chartTableService;

    /** @var int */
    private $chart;

    /**
     * @param ExcelFactory $excelService
     * @param EntityManagerInterface $em
     * @param ChartTableService $chartTableService
     */
    public function __construct(
        ExcelFactory $excelService,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        ChartTableService $chartTableService
    ) {
        parent::__construct($excelService, $em, $tokenStorage);

        $this->chartTableService = $chartTableService;
    }

    /**
     * Set chart
     *
     * @param int $chart
     */
    public function setChart(int $chart): void
    {
        $this->chart = $chart;
    }

    /**
     * {@inheritdoc}
     */
    protected function fillValues(): void
    {
        $table = $this->getChartTable();

        $this->fillHorizontalLabels($table);
        $this->fillVerticalLabels($table);
        $this->fillCells($table);
    }

    /**
     * Get chart table
     */
    private function getChartTable(): Table
    {
        return $this->chartTableService->getTableChart($this->chart, $this->filters);
    }

    /**
     * @param Table $table
     */
    private function fillHorizontalLabels(Table $table): void
    {
        $sheet = $this->excelObject->getActiveSheet();

        foreach ($table->getHLabels() as $columnPosition => $year) {
            $sheet->setCellValueByColumnAndRow(
                self::HORIZONTAL_LABEL_OFFSET + $columnPosition,
                self::HORIZONTAL_LABEL_ROW,
                $year
            );
        }
    }

    /**
     * @param Table $table
     */
    private function fillVerticalLabels(Table $table): void
    {
        $sheet = $this->excelObject->getActiveSheet();

        foreach ($table->getVLabels() as $rowPosition => $label) {
            $sheet->setCellValueByColumnAndRow(
                self::VERTICAL_LABEL_COLUMN,
                self::VERTICAL_LABEL_OFFSET + $rowPosition,
                $label
            );
        }
    }

    /**
     * @param Table $table
     */
    private function fillCells(Table $table): void
    {
        $sheet = $this->excelObject->getActiveSheet();

        $rowPosition = 0;
        foreach ($table->getCells() as $row) {
            $columnPosition = 0;

            foreach ($row as $value) {
                $excelCell = $sheet->setCellValueByColumnAndRow(
                    self::HORIZONTAL_LABEL_OFFSET + $columnPosition,
                    self::VERTICAL_LABEL_OFFSET + $rowPosition,
                    $value,
                    true
                );

                $this->setNumberFormat($excelCell, $table);

                $columnPosition++;
            }

            $rowPosition++;
        }
    }

    /**
     * @param PHPExcel_Cell $excelCell
     * @param Table $table
     */
    private function setNumberFormat($excelCell, $table): void
    {
        $numberFormat = $excelCell->getStyle()->getNumberFormat();

        if ($table->isPercentageFormat()) {
            $format = PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE;
        } else {
            $format = PHPExcel_Style_NumberFormat::FORMAT_NUMBER;
        }

        $numberFormat->setFormatCode($format);
    }
}
