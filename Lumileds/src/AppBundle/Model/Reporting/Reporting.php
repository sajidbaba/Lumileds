<?php

namespace AppBundle\Model\Reporting;

class Reporting
{
    const CHART_PARC_BY_SEGMENT = 1;
    const CHART_PARC_BY_REGION = 2;
    const CHART_PARC_BY_TECHNOLOGY = 3;
    const CHART_MARKET_VOLUME_BY_REGION = 4;
    const CHART_MARKET_SIZE_BY_REGION = 5;
    const CHART_MARKET_VOLUME_BY_SEGMENT = 6;
    const CHART_MARKET_SIZE_BY_SEGMENT = 7;
    const CHART_MARKET_VOLUME_BY_TECHNOLOGY = 8;
    const CHART_MARKET_SIZE_BY_TECHNOLOGY = 9;
    const CHART_MARKET_SHARE_BY_REGION = 10;
    const CHART_MARKET_SHARE_BY_TECHNOLOGY = 11;
    const CHART_PARC = 12;

    const DIVIDER = 1000000;
    const FORMAT_NORMAL = 1;
    const FORMAT_PERCENTAGE = 2;


    /** @var Chart */
    private $chart;

    /** @var Table */
    private $table;

    /** @var integer */
    private $number;

    /**
     * @param Chart $chart
     *
     * @return Reporting
     */
    public function setChart(Chart $chart): self
    {
        $this->chart = $chart;

        return $this;
    }

    /**
     * @param Table $table
     *
     * @return Reporting
     */
    public function setTable(Table $table): self
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @param int $number
     *
     * @return Reporting
     */
    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Format cells
     *
     * @param int $format
     *
     * @return Reporting
     */
    public function format($format = self::FORMAT_NORMAL): self
    {
        $this->chart->setFormat($format);
        $this->chart->format();

        if ($this->table) {
            $this->table->setFormat($format);
            $this->table->format();
        }

        return $this;
    }
}
