<?php

namespace AppBundle\Services;

use AppBundle\Entity\Cell;
use AppBundle\Entity\Indicator;
use AppBundle\Entity\Technology;
use AppBundle\Model\Reporting\Reporting;
use AppBundle\Model\Reporting\Table;
use AppBundle\Repository\CellRepository;
use Doctrine\ORM\EntityManagerInterface;

class ChartTableService
{
    /**
     * @var CellRepository
     */
    private $cellRepo;

    /**
     * TableService constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->cellRepo = $em->getRepository(Cell::class);
    }

    /**
     * Get chart table by id
     *
     * @param int $id
     * @param array $filters
     *
     * @return Table
     */
    public function getTableChart(int $id, array $filters): Table
    {
        $table = null;

        switch ($id) {
            case Reporting::CHART_PARC_BY_SEGMENT:
                $table = $this->getTableParcBySegment($filters);
                break;
            case Reporting::CHART_PARC_BY_REGION:
                $table = $this->getTableParcByRegion($filters);
                break;
            case Reporting::CHART_PARC_BY_TECHNOLOGY:
                $table = $this->getTableParcByTechnology($filters);
                break;
            case Reporting::CHART_MARKET_VOLUME_BY_REGION:
                $table = $this->getTableMarketVolumeByRegion($filters);
                break;
            case Reporting::CHART_MARKET_SIZE_BY_REGION:
                $table = $this->getTableMarketSizeByRegion($filters);
                break;
            case Reporting::CHART_MARKET_VOLUME_BY_SEGMENT:
                $table = $this->getTableMarketVolumeBySegment($filters);
                break;
            case Reporting::CHART_MARKET_SIZE_BY_SEGMENT:
                $table = $this->getTableMarketSizeBySegment($filters);
                break;
            case Reporting::CHART_MARKET_VOLUME_BY_TECHNOLOGY:
                $table = $this->getTableMarketVolumeByTechnology($filters);
                break;
            case Reporting::CHART_MARKET_SIZE_BY_TECHNOLOGY:
                $table = $this->getTableMarketSizeByTechnology($filters);
                break;
            case Reporting::CHART_MARKET_SHARE_BY_REGION:
                $table = $this->getTableMarketShareByRegion($filters);
                break;
            case Reporting::CHART_MARKET_SHARE_BY_TECHNOLOGY:
                $table = $this->getTableMarketShareByTechnology($filters);
                break;
        }

        return $table;
    }


    /**
     * Get chart table parc by segment
     *
     * @param array $filters
     *
     * @return Table
     */
    private function getTableParcBySegment(array $filters): Table
    {
        $data = $this->cellRepo->getParcBySegment($filters);
        $table = $this->convertTableParcBySegment($data);

        return $table->format();
    }

    /**
     * Get chart table parc by segment
     *
     * @param array $filters
     *
     * @return Table
     */
    private function getTableParcByRegion(array $filters): Table
    {
        $data = $this->cellRepo->getParcByRegion($filters);
        $table = $this->convertTableParcByRegion($data);

        return $table->format();
    }

    /**
     * Get chart table parc by technology
     *
     * @param array $filters
     *
     * @return Table
     */
    private function getTableParcByTechnology(array $filters): Table
    {
        $data = $this->cellRepo->getParcByTechnology($filters);
        $data = ReportingManager::addHLHalogen($data);
        $table = $this->convertTableParcByTechnology($data);

        return $table->setFormat(Reporting::FORMAT_PERCENTAGE);
    }

    /**
     * Get chart table market volume by region
     *
     * @param array $filters
     *
     * @return Table
     */
    private function getTableMarketVolumeByRegion(array $filters): Table
    {
        $data = $this->cellRepo->getMarketVolumeByRegion($filters);
        $table = $this->convertTableMarketVolumeByRegion($data);

        return $table->format();
    }

    /**
     * Get chart table market volume by segment
     *
     * @param array $filters
     *
     * @return Table
     */
    private function getTableMarketVolumeBySegment($filters): Table
    {
        $data = $this->cellRepo->getMarketVolumeBySegment($filters);
        $table = $this->convertTableMarketVolumeBySegment($data);

        return $table->format();
    }

    /**
     * Get chart table market size by region
     *
     * @param array $filters
     *
     * @return Table
     */
    private function getTableMarketSizeByRegion(array $filters): Table
    {
        $data = $this->cellRepo->getMarketSizeByRegion($filters);
        $table = $this->convertTableMarketSizeByRegion($data);

        return $table->format();
    }

    /**
     * Get chart table market size by segment
     *
     * @param array $filters
     *
     * @return Table
     */
    private function getTableMarketSizeBySegment($filters): Table
    {
        $data = $this->cellRepo->getMarketSizeBySegment($filters);
        $table = $this->convertTableMarketSizeBySegment($data);

        return $table->format();
    }

    /**
     * Get chart table market volume by technology
     *
     * @param array $filters
     *
     * @return Table
     */
    private function getTableMarketVolumeByTechnology(array $filters): Table
    {
        $data = $this->cellRepo->getMarketVolumeByTechnology($filters);
        $table = $this->convertTableMarketVolumeByTechnology($data);

        return $table->format();
    }

    /**
     * Get chart table market size by technology
     *
     * @param array $filters
     *
     * @return Table
     */
    private function getTableMarketSizeByTechnology(array $filters): Table
    {
        $data = $this->cellRepo->getMarketSizeByTechnology($filters);
        $table = $this->convertTableMarketSizeByTechnology($data);

        return $table->format();
    }

    /**
     * Get chart table market share by region
     *
     * @param $filters
     *
     * @return Table
     */
    private function getTableMarketShareByRegion(array $filters): Table
    {
        $data = $this->cellRepo->getMarketShareByRegion($filters);
        $table = $this->convertTableMarketShareByRegion($data);

        return $table->setFormat(Reporting::FORMAT_PERCENTAGE);
    }

    /**
     * Get chart table market share by technology
     *
     * @param $filters
     *
     * @return Table
     */
    private function getTableMarketShareByTechnology(array $filters): Table
    {
        $data = $this->cellRepo->getMarketShareByTechnology($filters);
        $table = $this->convertTableMarketShareByTechnology($data);

        return $table->setFormat(Reporting::FORMAT_PERCENTAGE);
    }

    /**
     * Convert array to chart table parc by segment
     *
     * @param array $data
     *
     * @return Table
     */
    public function convertTableParcBySegment(array $data): Table
    {
        $table = new Table();

        foreach ($data as $field) {
            $table
                ->addHLabel($field['year'])
                ->addVLabel($field['name'])
                ->addCellByLabels(
                    $field['1'],
                    $field['name'],
                    $field['year']
                );
        }

        $table
            ->showTotal()
            ->showGrowthRate();

        return $table;
    }

    /**
     * Convert array to chart table
     *
     * @param array $data
     *
     * @return Table
     */
    public function convertTableSimpleIndicator(array $data): Table
    {
        $table = new Table();

        foreach ($data as $year => $value) {
            $table
                ->addHLabel($year)
                ->addVLabel($year)
                ->addCellByLabels(
                    $value,
                    $year,
                    $year
                );
        }

        $table->showTotal();

        return $table;
    }

    /**
     * Convert array to chart table parc by region
     * @param array $data
     *
     * @return Table
     */
    public function convertTableParcByRegion(array $data): Table
    {
        $table = new Table();

        foreach ($data as $field) {
            $table
                ->addHLabel($field['year'])
                ->addVLabel($field['name'])
                ->addCellByLabels(
                    $field['1'],
                    $field['name'],
                    $field['year']
                );
        }

        $table
            ->showTotal()
            ->showGrowthRate();

        return $table;
    }

    /**
     * Convert array to chart table parc by technology
     *
     * @param array $data
     *
     * @return Table
     */
    public function convertTableParcByTechnology(array $data): Table
    {
        $table = new Table();

        foreach ($data as $field) {
            $table
                ->addHLabel($field['year'])
                ->addVLabel($field['techName'])
                ->addCellByLabels(
                    round($field['value'], 2),
                    $field['techName'],
                    $field['year']
                );
        }

        return $table;
    }

    /**
     * Convert array to chart table market volume by region
     *
     * @param array $data
     *
     * @return Table
     */
    public function convertTableMarketVolumeByRegion(array $data): Table
    {
        $table = new Table();

        foreach ($data as $field) {
            $table
                ->addHLabel($field['year'])
                ->addVLabel($field['region'])
                ->addCellByLabels(
                    $field['value'],
                    $field['region'],
                    $field['year']
                );
        }

        $table
            ->showTotal()
            ->showGrowthRate();

        return $table;
    }

    /**
     * Convert array to chart table market volume by segment
     *
     * @param array $data
     *
     * @return Table
     */
    public function convertTableMarketVolumeBySegment(array $data): Table
    {
        $table = new Table();

        foreach ($data as $field) {
            $table
                ->addHLabel($field['year'])
                ->addVLabel($field['segment'])
                ->addCellByLabels(
                    $field['value'],
                    $field['segment'],
                    $field['year']
                );
        }

        $table
            ->showTotal()
            ->showGrowthRate();

        return $table;
    }

    /**
     * Convert array to chart table market size by region
     *
     * @param array $data
     *
     * @return Table
     */
    public function convertTableMarketSizeByRegion(array $data): Table
    {
        $table = new Table();

        foreach ($data as $field) {
            $table
                ->addHLabel($field['year'])
                ->addVLabel($field['region'])
                ->addCellByLabels(
                    $field['value'],
                    $field['region'],
                    $field['year']
                );
        }

        $table
            ->showTotal()
            ->showGrowthRate();

        return $table;
    }

    /**
     * Convert array to chart table market size by segment
     *
     * @param array $data
     *
     * @return Table
     */
    public function convertTableMarketSizeBySegment(array $data): Table
    {
        $table = new Table();

        foreach ($data as $field) {
            $table
                ->addHLabel($field['year'])
                ->addVLabel($field['segment'])
                ->addCellByLabels(
                    $field['value'],
                    $field['segment'],
                    $field['year']
                );
        }

        $table
            ->showTotal()
            ->showGrowthRate();

        return $table;
    }

    /**
     * Convert array to chart table market volume by technology
     *
     * @param array $data
     *
     * @return Table
     */
    public function convertTableMarketVolumeByTechnology(array $data): Table
    {
        $labels = [
            Technology::TECHNOLOGY_HL_HALOGEN => 'HL Halogen',
            Technology::TECHNOLOGY_HL_LED_RF => 'HL LED RF',
            Technology::TECHNOLOGY_HL_NON_HALOGEN => 'HL Non-Halogen',
            Technology::TECHNOLOGY_HL_XENON => 'HL Xenon',
            Technology::TECHNOLOGY_SL_LED_RF => 'SL LED RF',
            Technology::TECHNOLOGY_SL_HIPER => 'SL Hiper',
            Technology::TECHNOLOGY_SL_CONV => 'SL Conventional',
        ];

        $table = new Table();

        foreach ($data as $field) {
            $value = $field['value'];
            $technology = $labels[$field['technology']] ?? '';
            $year = $field['year'];

            $table
                ->addHLabel($field['year'])
                ->addVLabel($technology)
                ->addCellByLabels(
                    $value,
                    $technology,
                    $year
                );
        }

        $table
            ->sort(array_values($labels))
            ->showTotal()
            ->showGrowthRate();

        return $table;
    }

    /**
     * Convert array to chart table market size by technology
     *
     * @param array $data
     *
     * @return Table
     */
    public function convertTableMarketSizeByTechnology(array $data): Table
    {
        $labels = [
            Technology::TECHNOLOGY_HL_HALOGEN => 'HL Halogen',
            Technology::TECHNOLOGY_HL_LED_RF => 'HL LED RF',
            Technology::TECHNOLOGY_HL_NON_HALOGEN => 'HL Non-Halogen',
            Technology::TECHNOLOGY_HL_XENON => 'HL Xenon',
            Technology::TECHNOLOGY_SL_LED_RF => 'SL LED RF',
            Technology::TECHNOLOGY_SL_HIPER => 'SL Hiper',
            Technology::TECHNOLOGY_SL_CONV => 'SL Conventional',
        ];

        $table = new Table();

        foreach ($data as $field) {
            $value = $field['value'];
            $technology = $labels[$field['technology']] ?? '';
            $year = $field['year'];

            $table
                ->addHLabel($field['year'])
                ->addVLabel($technology)
                ->addCellByLabels(
                    $value,
                    $technology,
                    $year
                );
        }

        $table
            ->sort(array_values($labels))
            ->showTotal()
            ->showGrowthRate();

        return $table;
    }

    /**
     * Convert array to chart table market share by region
     *
     * @param array $data
     *
     * @return Table
     */
    public function convertTableMarketShareByRegion(array $data): Table
    {
        $table = new Table();

        $totals = [];

        foreach ($data as $field) {
            $value = $field['marketValue'] != 0 ? $field['llSales'] / $field['marketValue'] : 0;

            $table
                ->addHLabel($field['year'])
                ->addVLabel($field['region'])
                ->addCellByLabels(
                    $value,
                    $field['region'],
                    $field['year']
                );

            if (!isset($totals[$field['year']])) {
                $totals[$field['year']] = ['totalLLSales' => 0, 'totalMarketValue' => 0];
            }

            $totals[$field['year']]['totalLLSales'] += $field['llSales'];
            $totals[$field['year']]['totalMarketValue'] += $field['marketValue'];
        }

        foreach ($totals as $vLabel => $totalRow) {
            if ($totalRow['totalMarketValue'] == 0) {
                $total = 0;
            } else {
                $total = $totalRow['totalLLSales'] / $totalRow['totalMarketValue'];
            }

            $table->addTotalByLabel($total, $vLabel);
        }

        $table
            ->showTotal();

        return $table;
    }

    /**
     * Convert array to chart table market share by technology
     *
     * @param array $data
     *
     * @return Table
     */
    public function convertTableMarketShareByTechnology(array $data): Table
    {
        $labels = [
            Technology::TECHNOLOGY_HL_HALOGEN => 'HL Halogen',
            Technology::TECHNOLOGY_HL_LED_RF => 'HL LED RF',
            Technology::TECHNOLOGY_HL_XENON => 'HL Xenon',
            Technology::TECHNOLOGY_SL_LED_RF => 'SL LED RF',
            Technology::TECHNOLOGY_SL_HIPER => 'SL Hiper',
            Technology::TECHNOLOGY_SL_CONV => 'SL Conventional',
        ];

        $table = new Table();

        $totals = [];

        foreach ($data as $field) {
            $technology = $labels[$field['technology']] ?? '';
            $value = $field['value'] ?? 0;

            if (!isset($totals[$field['year']])) {
                $totals[$field['year']] = [
                    'totalLLSales' => 0,
                    'totalMarketValue' => 0,
                ];
            }

            $totals[$field['year']]['totalLLSales'] += $field['llSales'];
            $totals[$field['year']]['totalMarketValue'] += $field['marketValue'];

            $table
                ->addHLabel($field['year'])
                ->addVLabel($technology)
                ->addCellByLabels(
                    $value,
                    $technology,
                    $field['year']
                );
        }

        foreach ($totals as $vLabel => $totalRow) {
            if ($totalRow['totalMarketValue'] == 0) {
                $total = 0;
            } else {
                $total = $totalRow['totalLLSales'] / $totalRow['totalMarketValue'];
            }

            $table->addTotalByLabel($total, $vLabel);
        }

        $table
            ->showTotal();

        return $table;
    }

    /**
     * @param array $data
     * @param Indicator $indicator
     *
     * @return Table
     */
    public function convertTableSimpleIndicatorChart(array $data, Indicator $indicator): Table
    {
        $table = new Table();

        foreach ($data as $year => $value) {
            $table
                ->addHLabel($year)
                ->addVLabel($indicator->getName())
                ->addCellByLabels(
                    $value,
                    'main',
                    $year
                );
        }

        return $table;
    }
}
