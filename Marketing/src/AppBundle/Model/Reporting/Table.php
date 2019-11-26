<?php

namespace AppBundle\Model\Reporting;

use JMS\Serializer\Annotation as JMS;

class Table
{
    const GROWTH_RATE_KEY = 'gr';

    /**
     * @JMS\Type("array")
     * @JMS\SerializedName("hLabels")
     *
     * @var array Horizontal Labels
     */
    public $hLabels = [];

    /**
     * @JMS\Type("array")
     * @JMS\SerializedName("vLabels")
     *
     * @var array Vertical Labels
     */
    public $vLabels = [];

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    public $cells = [];

    /**
     * @var array
     */
    private $total = [];

    /**
     * @var int
     */
    private $format = Reporting::FORMAT_NORMAL;

    /**
     * @return array
     */
    public function getHLabels(): array
    {
        return $this->hLabels;
    }

    /**
     * @param array $hLabels
     */
    public function setHLabels(array $hLabels): void
    {
        $this->hLabels = $hLabels;
    }

    /**
     * @param string $label
     *
     * @return Table
     */
    public function addHLabel(string $label): self
    {
        if (!in_array($label, $this->hLabels)) {
            $this->hLabels[] = $label;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getVLabels(): array
    {
        return $this->vLabels;
    }

    /**
     * @param array $vLabels
     */
    public function setVLabels(array $vLabels): void
    {
        $this->vLabels = $vLabels;
    }

    /**
     * @return array
     */
    public function getCells(): array
    {
        return $this->cells;
    }

    /**
     * @param array $cells
     */
    public function setCells(array $cells): void
    {
        $this->cells = $cells;
    }

    /**
     * @param string $label
     *
     * @return Table
     */
    public function addVLabel(string $label): self
    {
        if (!in_array($label, $this->vLabels)) {
            $this->vLabels[] = $label;
        }

        return $this;
    }

    /**
     * @param string $value
     * @param string $hLabel
     * @param string $vLabel
     *
     * @return Table
     */
    public function addCellByLabels(string $value, string $hLabel, string $vLabel): self
    {
        if (array_key_exists($vLabel, $this->total)) {
            $this->total[$vLabel] += (float) $value;
        } else {
            $this->total[$vLabel] = $value;
        }

        $this->cells[$hLabel][$vLabel] = $value;

        return $this;
    }

    /**
     * @param string $value
     * @param string $vLabel
     *
     * @return Table
     */
    public function addTotalByLabel(string $value, string $vLabel): self
    {
        $this->total[$vLabel] = $value;

        return $this;
    }


    /**
     * Set format
     *
     * @param int $format
     *
     * @return Table
     */
    public function setFormat(int $format): self
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Is percentage format
     *
     * @return int
     */
    public function isPercentageFormat(): int
    {
        return $this->format === Reporting::FORMAT_PERCENTAGE;
    }

    /**
     * Add Total row to table
     *
     * @return Table
     */
    public function showTotal(): self
    {
        $this->addVLabel('Total');
        $this->cells['total'] = $this->total;

        return $this;
    }

    /**
     * Add Growth Rate to table
     * Formula: ((<last value> / <first value>) ^ (1 / (<last year> - <first year>)) - 1
     *
     * @return Table
     */
    public function showGrowthRate(): self
    {
        if (empty(array_filter($this->cells))) {
            return $this;
        }

        $this->addHLabel('CAGR');

        foreach ($this->cells as &$row) {
            $firstValue = current($row);
            $firstYear = key($row);
            $lastValue = end($row);
            $lastYear = key($row);

            if ($firstValue == 0) {
                $growthRate = 0;
            } else {
                $diffValue = $lastValue / $firstValue;
                $sign = $diffValue < 0 ? -1 : 1;

                $growthRate = $sign * (abs($diffValue) ** (1 / ($lastYear - $firstYear))) - 1;
            }


            $row[self::GROWTH_RATE_KEY] = $growthRate;
        }

        return $this;
    }

    /**
     * Format values
     *
     * @return Table
     */
    public function format(): self
    {
        if ($this->format === Reporting::FORMAT_PERCENTAGE) {
            $callback = function (&$value) {
                $value = round($value * 100).'%';
            };
        } else {
            $callback = function (&$value, $key) {
                if (self::GROWTH_RATE_KEY === $key) {
                    $value = number_format($value * 100, 1).'%';
                } else {
                    $value = round($value / Reporting::DIVIDER,2);
                }
            };
        }

        array_walk_recursive($this->cells, $callback);

        return $this;
    }

    /**
     * @param array $order
     *
     * @return self
     */
    public function sort(array $order): self
    {
        usort($this->vLabels, function ($label1, $label2) use ($order) {
            return array_search($label1, $order) <=> array_search($label2, $order);
        });

        uksort($this->cells, function ($key1, $key2) use ($order) {
            return array_search($key1, $order) <=> array_search($key2, $order);
        });

        return $this;
    }
}
