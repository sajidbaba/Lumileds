<?php

namespace AppBundle\Model\Reporting;

use JMS\Serializer\Annotation as JMS;

class Chart
{
    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    protected $labels = [];

    /**
     * @JMS\Accessor(getter="getDatasets")
     * @JMS\Type("array")
     *
     * @var Dataset[]
     */
    protected $datasets = [];

    /**
     * @var int
     */
    private $format = Reporting::FORMAT_NORMAL;

    /**
     * @param $label
     *
     * @return Chart
     */
    public function addLabel($label): self
    {
        if (!in_array($label, $this->labels)) {
            $this->labels[] = $label;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * @param string|int $key
     * @param Dataset $dataSet
     *
     * @return Chart
     */
    public function addDataset($key, Dataset $dataSet): self
    {
        $this->datasets[$key] = $dataSet;

        return $this;
    }

    /**
     * @return Dataset[]
     */
    public function getDatasets(): array
    {
        return array_values($this->datasets);
    }

    /**
     * @param mixed $key
     *
     * @return Dataset|null
     */
    public function getDataset($key): ?Dataset
    {
        return $this->datasets[$key] ?? null;
    }

    /**
     * Set format
     *
     * @param int $format
     *
     * @return Chart
     */
    public function setFormat(int $format): self
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Format values
     *
     * @return Chart
     */
    public function format(): self
    {
        if ($this->format === Reporting::FORMAT_PERCENTAGE) {
            $callback = function ($value) {
                return round($value);
            };
        } else {
            $callback = function ($value) {
                return round($value / Reporting::DIVIDER);
            };
        }

        foreach ($this->datasets as &$dataset) {
            $data = $dataset->getData();
            foreach ($data as &$value) {
                $value = $callback($value);
            }
            $dataset->setData($data);
        }

        return $this;
    }

    /**
     * @param array $order
     */
    public function sort(array $order): void
    {
        uksort($this->datasets, function ($key1, $key2) use ($order) {
            return array_search($key1, $order) <=> array_search($key2, $order);
        });
    }
}
