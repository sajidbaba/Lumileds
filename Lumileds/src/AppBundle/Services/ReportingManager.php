<?php

namespace AppBundle\Services;

use AppBundle\Entity\Cell;
use AppBundle\Entity\Indicator;
use AppBundle\Entity\SavedFilter;
use AppBundle\Entity\Setting;
use AppBundle\Entity\Technology;
use AppBundle\Entity\User;
use AppBundle\Model\Reporting\Chart;
use AppBundle\Model\Reporting\Dataset;
use AppBundle\Model\Reporting\Reporting;
use Doctrine\ORM\EntityManagerInterface;

class ReportingManager
{
    private const COLORS = [
        '#2196F3',
        '#388E3C',
        '#F9A825',
        '#FF3D00',
        '#6D4C41',
        '#303F9F',
        '#D81B60',
        '#4527A0',
        '#b71c1c',
        '#6A1B9A',
        '#3DB858',
    ];

    /** @var EntityManagerInterface */
    private $em;

    /** @var ChartTableService */
    private $chartTableService;

    /**
     * @param EntityManagerInterface $em
     * @param ChartTableService $chartTableService
     */
    public function __construct(EntityManagerInterface $em, ChartTableService $chartTableService)
    {
        $this->em = $em;
        $this->chartTableService = $chartTableService;
    }

    /**
     * Get chart Parc by Technology
     *
     * @param User $user
     * @param array $filters
     *
     * @return Reporting
     */
    public function getParcByTechnology(User $user, array $filters): Reporting
    {
        $colors = [
            Technology::TECHNOLOGY_HL_HALOGEN => '#149B9E',
            Technology::TECHNOLOGY_HL_NON_HALOGEN => '#F16A21',
            Technology::TECHNOLOGY_HL_XENON => '#75B443',
            Technology::TECHNOLOGY_HL_LED => '#ACD48C',
            Technology::TECHNOLOGY_HL_LED_RF => '#7C51A1',
        ];

        $this->saveFilters($user, $filters);

        $filters = $this->prepareFilters($user, $filters);

        $data = $this->em->getRepository(Cell::class)->getParcByTechnology($filters);
        $data = $this->addHLHalogen($data);

        $table = $this->chartTableService->convertTableParcByTechnology($data);

        $chart = new Chart();

        foreach ($data as $field) {
            $techId = $field['techId'] ?? $field['techName'];
            $techName = $field['techName'];

            $dataset = $chart->getDataset($techId);
            if (!$dataset) {
                $dataset = (new Dataset())
                    ->setLabel($techName)
                    ->setBorderColor($colors[$techId] ?? '')
                    ->setFill(false);

                $chart->addDataset($techId, $dataset);
            }

            $dataset->addData(round($field['value'] * 100));
            $chart->addLabel($field['year']);
        }


        $reporting = (new Reporting())
            ->setNumber(Reporting::CHART_PARC_BY_TECHNOLOGY)
            ->setChart($chart)
            ->setTable($table)
            ->format(Reporting::FORMAT_PERCENTAGE);

        return $reporting;
    }


    /**
     * Get chart Parc by Segment
     *
     * @param User $user
     * @param array $filters
     *
     * @return Reporting
     */
    public function getParcBySegment(User $user, array $filters): Reporting
    {
        $this->saveFilters($user, $filters);

        $filters = $this->prepareFilters($user, $filters);

        $res = $this->em->getRepository(Cell::class)->getParcBySegment($filters);
        $table = $this->chartTableService->convertTableParcBySegment($res);

        $chart = new Chart();
        foreach ($res as $result) {
            $dataset = $chart->getDataset($result['name']);

            if (!$dataset) {
                $dataset = (new Dataset())
                    ->setType('bar')
                    ->setBackgroundColor($this->getSegmentColor($result['name']))
                    ->setLabel($result['name']);

                $chart->addDataset($result['name'], $dataset);
            }

            $dataset->addData($result[1]);
            $chart->addLabel($result['year']);
        }

        $reporting = (new Reporting())
            ->setNumber(Reporting::CHART_PARC_BY_SEGMENT)
            ->setChart($chart)
            ->setTable($table)
            ->format();

        return $reporting;
    }


    /**
     * Get chart Parc
     *
     * @param array $filters
     *
     * @return Reporting
     */
    public function getSimpleIndicatorChart(array $filters): Reporting
    {
        $data = $this->em->getRepository(Cell::class)->getSimpleIndicatorChartData($filters);

        $affectedCells = $filters['affectedCells'] ?? null;
        if (is_array($affectedCells)) {
            foreach ($affectedCells as $affectedCell) {
                if (isset($data[$affectedCell['id']])) {
                    $data[$affectedCell['id']]['value'] = $affectedCell['value'];
                }
            }
        }

        $res = [];
        foreach ($data as $row) {
            if (!isset($res[$row['year']])) {
                $res[$row['year']] = 0;
            }

            $res[$row['year']] += (int)$row['value'];
        }

        $indicator = $this->em->getRepository(Indicator::class)->find($filters['indicator']);
        $table = $this->chartTableService->convertTableSimpleIndicatorChart($res, $indicator);

        $chart = new Chart();
        foreach ($res as $year => $value) {
            $dataset = $chart->getDataset('main');

            if (!$dataset) {
                $dataset = (new Dataset())
                    ->setType('bar')
                    ->setBackgroundColor('#B8E3FF');

                $chart->addDataset('main', $dataset);
            }

            $dataset->addData($value);
            $chart->addLabel($year);
        }

        $reporting = (new Reporting())
            ->setNumber(100 + ($filters['indicator'] ?? 0))
            ->setChart($chart)
            ->setTable($table)
            ->format();

        return $reporting;
    }

    /**
     * @param User $user
     * @param array $filters
     *
     * @return Reporting
     */
    public function getParcByRegion(User $user, array $filters): Reporting
    {
        $this->saveFilters($user, $filters);

        $filters = $this->prepareFilters($user, $filters);

        $res = $this->em->getRepository(Cell::class)->getParcByRegion($filters);
        $table = $this->chartTableService->convertTableParcByRegion($res);

        $chart = new Chart();

        foreach ($res as $result) {
            $dataset = $chart->getDataset($result['name']);

            if (!$dataset) {
                $dataset = (new Dataset())
                    ->setType('bar')
                    ->setBackgroundColor($this->getRegionColor($result['name']))
                    ->setLabel($result['name']);

                $chart->addDataset($result['name'], $dataset);
            }

            $dataset->addData($result[1]);
            $chart->addLabel($result['year']);
        }

        $reporting = (new Reporting())
            ->setNumber(Reporting::CHART_PARC_BY_REGION)
            ->setChart($chart)
            ->setTable($table)
            ->format();

        return $reporting;
    }

    /**
     * Get data for chart Market Volume by region
     *
     * @param User $user
     * @param array $filters
     *
     * @return Reporting
     */
    public function getMarketVolumeByRegion(User $user, array $filters): Reporting
    {
        $this->saveFilters($user, $filters);

        $filters = $this->prepareFilters($user, $filters);

        $data = $this->em->getRepository(Cell::class)->getMarketVolumeByRegion($filters);
        $table = $this->chartTableService->convertTableMarketVolumeByRegion($data);

        $chart = new Chart();

        foreach ($data as $field) {
            $year = $field['year'];
            $region = $field['region'];

            $dataset = $chart->getDataset($region);
            if (!$dataset) {
                $dataset = (new Dataset())
                    ->setLabel($region)
                    ->setBackgroundColor($this->getMarketVolumeRegionColor($region));

                $chart->addDataset($region, $dataset);
            }

            $dataset->addData($field['value']);
            $chart->addLabel($year);
        }

        $reporting = (new Reporting())
            ->setNumber(Reporting::CHART_MARKET_VOLUME_BY_REGION)
            ->setChart($chart)
            ->setTable($table)
            ->format();

        return $reporting;
    }

    /**
     * Get data for chart Market Size by region
     *
     * @param User $user
     * @param array $filters
     *
     * @return Reporting
     */
    public function getMarketSizeByRegion(User $user, array $filters): Reporting
    {
        $this->saveFilters($user, $filters);

        $filters = $this->prepareFilters($user, $filters);

        $data = $this->em->getRepository(Cell::class)->getMarketSizeByRegion($filters);
        $table = $this->chartTableService->convertTableMarketSizeByRegion($data);

        $chart = new Chart();

        foreach ($data as $field) {
            $year = $field['year'];
            $region = $field['region'];

            $dataset = $chart->getDataset($region);
            if (!$dataset) {
                $dataset = (new Dataset())
                    ->setLabel($region)
                    ->setBackgroundColor($this->getMarketSizeRegionColor($region));

                $chart->addDataset($region, $dataset);
            }

            $dataset->addData($field['value']);
            $chart->addLabel($year);
        }

        $reporting = (new Reporting())
            ->setNumber(Reporting::CHART_MARKET_SIZE_BY_REGION)
            ->setChart($chart)
            ->setTable($table)
            ->format();

        return $reporting;
    }

    /**
     * Get data for chart Market Volume by segment
     *
     * @param User $user
     * @param array $filters
     *
     * @return Reporting
     */
    public function getMarketVolumeBySegment(User $user, array $filters): Reporting
    {
        $this->saveFilters($user, $filters);

        $filters = $this->prepareFilters($user, $filters);

        $data = $this->em->getRepository(Cell::class)->getMarketVolumeBySegment($filters);
        $table = $this->chartTableService->convertTableMarketVolumeBySegment($data);

        $chart = new Chart();

        foreach ($data as $field) {
            $year = $field['year'];
            $segment = $field['segment'];

            $dataset = $chart->getDataset($segment);
            if (!$dataset) {
                $dataset = (new Dataset())
                    ->setLabel($segment)
                    ->setBackgroundColor($this->getMarketVolumeSegmentColor($segment));

                $chart->addDataset($segment, $dataset);
            }

            $dataset->addData($field['value']);
            $chart->addLabel($year);
        }

        $reporting = (new Reporting())
            ->setNumber(Reporting::CHART_MARKET_VOLUME_BY_SEGMENT)
            ->setChart($chart)
            ->setTable($table)
            ->format();

        return $reporting;
    }


    /**
     * Get data for chart Market Size by segment
     *
     * @param User $user
     * @param array $filters
     *
     * @return Reporting
     */
    public function getMarketSizeBySegment(User $user, array $filters): Reporting
    {
        $this->saveFilters($user, $filters);

        $filters = $this->prepareFilters($user, $filters);

        $data = $this->em->getRepository(Cell::class)->getMarketSizeBySegment($filters);
        $table = $this->chartTableService->convertTableMarketSizeBySegment($data);

        $chart = new Chart();

        foreach ($data as $field) {
            $year = $field['year'];
            $segment = $field['segment'];

            $dataset = $chart->getDataset($segment);
            if (!$dataset) {
                $dataset = (new Dataset())
                    ->setLabel($segment)
                    ->setBackgroundColor($this->getMarketSizeSegmentColor($segment));

                $chart->addDataset($segment, $dataset);
            }

            $dataset->addData($field['value']);
            $chart->addLabel($year);
        }

        $reporting = (new Reporting())
            ->setNumber(Reporting::CHART_MARKET_SIZE_BY_SEGMENT)
            ->setChart($chart)
            ->setTable($table)
            ->format();

        return $reporting;
    }

    /**
     * Get data for chart Market Volume by technology
     *
     * @param User $user
     * @param array $filters
     *
     * @return Reporting
     */
    public function getMarketVolumeByTechnology(User $user, array $filters): Reporting
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

        $this->saveFilters($user, $filters);

        $filters = $this->prepareFilters($user, $filters);

        $data = $this->em->getRepository(Cell::class)->getMarketVolumeByTechnology($filters);
        $table = $this->chartTableService->convertTableMarketVolumeByTechnology($data);

        $chart = new Chart();

        foreach ($data as $field) {
            $year = $field['year'];
            $technology = $field['technology'];

            $dataset = $chart->getDataset($technology);
            if (!$dataset) {
                $dataset = (new Dataset())
                    ->setLabel($labels[$technology] ?? '')
                    ->setBackgroundColor($this->getMarketVolumeTechnologyColor($technology));

                $chart->addDataset($technology, $dataset);
            }

            $dataset->addData($field['value']);
            $chart->addLabel($year);
        }

        $chart->sort(array_keys($labels));

        $reporting = (new Reporting())
            ->setNumber(Reporting::CHART_MARKET_VOLUME_BY_TECHNOLOGY)
            ->setChart($chart)
            ->setTable($table)
            ->format();

        return $reporting;
    }

    /**
     * Get data for chart Market Size by technology
     *
     * @param User $user
     * @param array $filters
     *
     * @return Reporting
     */
    public function getMarketSizeByTechnology(User $user, array $filters): Reporting
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

        $this->saveFilters($user, $filters);

        $filters = $this->prepareFilters($user, $filters);

        $data = $this->em->getRepository(Cell::class)->getMarketSizeByTechnology($filters);
        $table = $this->chartTableService->convertTableMarketSizeByTechnology($data);

        $chart = new Chart();

        foreach ($data as $field) {
            $year = $field['year'];
            $technology = $field['technology'];

            $dataset = $chart->getDataset($technology);
            if (!$dataset) {
                $dataset = (new Dataset())
                    ->setLabel($labels[$technology] ?? null)
                    ->setBackgroundColor($this->getMarketSizeTechnologyColor($technology));

                $chart->addDataset($technology, $dataset);
            }

            $dataset->addData($field['value']);
            $chart->addLabel($year);
        }

        $chart->sort(array_keys($labels));

        $reporting = (new Reporting())
            ->setNumber(Reporting::CHART_MARKET_SIZE_BY_TECHNOLOGY)
            ->setChart($chart)
            ->setTable($table)
            ->format();

        return $reporting;
    }

    /**
     * @param User $user
     * @param array $filters
     *
     * @return Reporting
     */
    public function getMarketShareByRegion(User $user, array $filters): Reporting
    {
        $this->saveFilters($user, $filters);

        $filters = $this->prepareFilters($user, $filters);

        $data = $this->em->getRepository(Cell::class)->getMarketShareByRegion($filters);
        $table = $this->chartTableService->convertTableMarketShareByRegion($data);

        $chart = new Chart();
        foreach ($data as $i => $field) {
            $year = $field['year'];
            $datasetLL = $chart->getDataset($year);
            $datasetCompetition = $chart->getDataset('competitors-' . $year);

            if (!$datasetLL) {
                $datasetLL = (new Dataset())
                    ->setLabel($year)
                    ->setStack($year)
                    ->setBackgroundColor(self::COLORS[$i] ?? '#2BAAFF');

                $chart->addDataset($year, $datasetLL);
            }

            if ($field['marketValue'] == 0) {
                $value = 0;
            } else {
                $value = round(($field['llSales'] / $field['marketValue']) * 100);
            }

            $datasetLL->addData($value);
            $chart->addLabel($field['region']);

            if (!$datasetCompetition) {
                $datasetCompetition = (new Dataset())
                    ->setLabel('Competitors')
                    ->setStack($year)
                    ->setBackgroundColor('#FFFFFF');

                $chart->addDataset('competitors-' . $year, $datasetCompetition);
            }

            $datasetCompetition->addData(100 - $value);
        }

        $reporting = (new Reporting())
            ->setNumber(Reporting::CHART_MARKET_SHARE_BY_REGION)
            ->setChart($chart)
            ->setTable($table)
            ->format(Reporting::FORMAT_PERCENTAGE);

        return $reporting;
    }

    /**
     * @param User $user
     * @param array $filters
     *
     * @return Reporting
     */
    public function getMarketShareByTechnology(User $user, array $filters): Reporting
    {
        $labels = [
            Technology::TECHNOLOGY_HL_HALOGEN => 'HL Halogen',
            Technology::TECHNOLOGY_HL_LED_RF => 'HL LED RF',
            Technology::TECHNOLOGY_HL_XENON => 'HL Xenon',
            Technology::TECHNOLOGY_SL_LED_RF => 'SL LED RF',
            Technology::TECHNOLOGY_SL_HIPER => 'SL Hiper',
            Technology::TECHNOLOGY_SL_CONV => 'SL Conventional',
        ];

        $this->saveFilters($user, $filters);

        $filters = $this->prepareFilters($user, $filters);

        $data = $this->em->getRepository(Cell::class)->getMarketShareByTechnology($filters);
        $table = $this->chartTableService->convertTableMarketShareByTechnology($data);

        $chart = new Chart();

        foreach ($data as $i => $field) {
            $year = $field['year'];
            $datasetLL = $chart->getDataset($year);
            $datasetCompetition = $chart->getDataset('competitors-' . $year);

            if (!$datasetLL) {
                $datasetLL = (new Dataset())
                    ->setLabel($year)
                    ->setStack($year)
                    ->setBackgroundColor(self::COLORS[$i] ?? '#2BAAFF');

                $chart->addDataset($year, $datasetLL);
            }

            $datasetLL->addData(round($field['value'] * 100));
            $chart->addLabel($labels[$field['technology']] ?? null);

            if (!$datasetCompetition) {
                $datasetCompetition = (new Dataset())
                    ->setLabel('Competitors')
                    ->setStack($year)
                    ->setBackgroundColor('#FFFFFF');

                $chart->addDataset('competitors-' . $year, $datasetCompetition);
            }

            $datasetCompetition->addData(100 - round($field['value'] * 100));
        }

        $reporting = (new Reporting())
            ->setNumber(Reporting::CHART_MARKET_SHARE_BY_TECHNOLOGY)
            ->setChart($chart)
            ->setTable($table)
            ->format(Reporting::FORMAT_PERCENTAGE);

        return $reporting;
    }

    /**
     * Get color by region
     *
     * @param string $region
     *
     * @return string
     */
    private function getRegionColor(string $region): string
    {
        $colors = [
            'APAC' => '#3A5A21',
            'EMEA' => '#588732',
            'LATAM' => '#C8E2B2',
            'NAFTA' => '#E3F1D9',
            'Greater China' => '#ACD48C',
        ];

        return $colors[$region] ?? '';
    }

    /**
     * Get color by region segment
     *
     * @param string $region
     *
     * @return string
     */
    private function getMarketSizeRegionColor(string $region): string
    {
        $colors = [
            'APAC' => '#3A5A21',
            'EMEA' => '#588732',
            'LATAM' => '#C8E2B2',
            'NAFTA' => '#E3F1D9',
            'Greater China' => '#ACD48C',
        ];

        return $colors[$region] ?? '';
    }

    /**
     * Get color by region volume
     *
     * @param string $region
     *
     * @return string
     */
    private function getMarketVolumeRegionColor(string $region): string
    {
        $colors = [
            'APAC' => '#002F4E',
            'EMEA' => '#004676',
            'LATAM' => '#72C6FF',
            'NAFTA' => '#B8E3FF',
            'Greater China' => '#2BAAFF',
        ];

        return $colors[$region] ?? '';
    }

    /**
     * Get color by segment
     *
     * @param string $segment
     *
     * @return string
     */
    private function getSegmentColor(string $segment): string
    {
        $colors = [
            '2W' => '#B8E3FF',
            'LV' => '#2BAAFF',
            'HV' => '#72C6FF',
        ];

        return $colors[$segment] ?? '';
    }

    /**
     * Get color by segment market volume
     *
     * @param string $segment
     *
     * @return string
     */
    private function getMarketVolumeSegmentColor(string $segment): string
    {
        $colors = [
            '2W' => '#B8E3FF',
            'LV' => '#2BAAFF',
            'HV' => '#72C6FF',
        ];

        return $colors[$segment] ?? '';
    }

    /**
     * Get color by segment market size
     *
     * @param string $segment
     *
     * @return string
     */
    private function getMarketSizeSegmentColor(string $segment): string
    {
        $colors = [
            '2W' => '#E3F1D9',
            'LV' => '#ACD48C',
            'HV' => '#C8E2B2',
        ];

        return $colors[$segment] ?? '';
    }

    /**
     * Get color by technology market volume
     *
     * @param string $technology
     *
     * @return string
     */
    private function getMarketVolumeTechnologyColor(string $technology): string
    {
        $colors = [
            Technology::TECHNOLOGY_HL_HALOGEN => '#05395B',
            Technology::TECHNOLOGY_HL_LED_RF => '#005E9D',
            Technology::TECHNOLOGY_HL_NON_HALOGEN => '#2482C1',
            Technology::TECHNOLOGY_HL_XENON => '#2BAAFF',
            Technology::TECHNOLOGY_SL_LED_RF => '#72C6FF',
            Technology::TECHNOLOGY_SL_HIPER => '#B8E3FF',
            Technology::TECHNOLOGY_SL_CONV => '#DEEEF9',
        ];

        return $colors[$technology] ?? '';
    }

    /**
     * Get color by technology market size
     *
     * @param string $technology
     *
     * @return string
     */
    private function getMarketSizeTechnologyColor(string $technology): string
    {
        $colors = [
            Technology::TECHNOLOGY_HL_HALOGEN => '#24420D',
            Technology::TECHNOLOGY_HL_LED_RF => '#3E6024',
            Technology::TECHNOLOGY_HL_NON_HALOGEN => '#588732',
            Technology::TECHNOLOGY_HL_XENON => '#7FA85E',
            Technology::TECHNOLOGY_SL_LED_RF => '#ACD48C',
            Technology::TECHNOLOGY_SL_HIPER => '#C8E2B2',
            Technology::TECHNOLOGY_SL_CONV => '#E3F1D9',
        ];

        return $colors[$technology] ?? '';
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public static function addHLHalogen(array $data): array
    {
        $years = array_unique(array_map(function (array $row) {
            return $row['year'];
        }, $data));

        $halogen = array_fill_keys($years, 1);

        foreach ($data as $row) {
            $halogen[$row['year']] -= $row['value'];
        }

        foreach ($halogen as $year => $value) {
            $data[] = [
                'year' => $year,
                'value' => $value,
                'techId' => Technology::TECHNOLOGY_HL_HALOGEN,
                'techName' => 'HL Halogen',
            ];
        }

        return $data;
    }

    /**
     * @param User $user
     * @param array $filters
     *
     * @return array
     */
    private function prepareFilters(User $user, array $filters): array
    {
        if (!$user->isAdmin()) {
            $userReportingFromYear = $this->em->getRepository(Setting::class)->findOneBy(['key' => Setting::USER_REPORTING_FROM_YEAR]);
            $userReportingToYear = $this->em->getRepository(Setting::class)->findOneBy(['key' => Setting::USER_REPORTING_TO_YEAR]);

            $allowedYears = range($userReportingFromYear->getValue(), $userReportingToYear->getValue());

            if (!isset($filters['years']) || empty($filters['years'])) {
                $filters['years'] = $allowedYears;
            } else {
                $filters['years'] = array_intersect($filters['years'], $allowedYears);
            }
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

        $savedFilter->setContributionFilter($filters);

        $this->em->flush();
    }
}
