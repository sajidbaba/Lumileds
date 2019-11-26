<?php

namespace AppBundle\Model;

use JMS\Serializer\Annotation as JMS;

class Table
{
    /**
     * @JMS\Accessor(getter="getRows")
     * @var Row[]
     */
    protected $rows = [];

    /**
     * @JMS\Type("array<string>")
     * @var array
     */
    protected $years = [];

    /**
     * @JMS\Type("bool")
     *
     * @var bool
     */
    protected $approved;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $instruction;

    /**
     * @param Cell $cell
     */
    public function addCell(Cell $cell)
    {
        $this->addYear($cell->getYear());

        $row = $this->getRow($cell);
        $row->addCell($cell);
    }

    /**
     * @param string $year
     *
     * @return Table
     */
    protected function addYear($year): self
    {
        if (!in_array($year, $this->years)) {
            $this->years[] = $year;
        }

        return $this;
    }

    /**
     * Get rows
     *
     * @return Row[]
     */
    public function getRows(): array
    {
        return array_values($this->rows);
    }

    /**
     * Get years
     *
     * @return string[]
     */
    public function getYears(): array
    {
        return $this->years;
    }

    /**
     * Set years
     *
     * @param array $years
     *
     * @return Table
     */
    public function setYears(array $years): self
    {
        $this->years = $years;

        return $this;
    }

    /**
     * @param Cell $cell
     *
     * @return Row
     */
    protected function getRow(Cell $cell): Row
    {
        $row = (new Row())
            ->setMarket($cell->getCountry())
            ->setSegment($cell->getSegment())
            ->setIndicator($cell->getIndicator())
            ->setTechnology($cell->getTechnology());

        $key = $row->getKey();

        if (array_key_exists($key, $this->rows)) {
            $row = $this->rows[$key];
        } else {
            $row = $this->addRow($key, $row);
        }

        return $row;
    }

    /**
     * @param string $key
     * @param Row $row
     *
     * @return Row
     */
    protected function addRow(string $key, Row $row): Row
    {
        $this->rows[$key] = $row;

        return $row;
    }

    /**
     * @return bool
     */
    public function isApproved(): ?bool
    {
        return $this->approved;
    }

    /**
     * @param bool $approved
     *
     * @return self
     */
    public function setApproved(bool $approved): self
    {
        $this->approved = $approved;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Table
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstruction(): string
    {
        return $this->instruction;
    }

    /**
     * @param string $instruction
     *
     * @return Table
     */
    public function setInstruction(string $instruction): self
    {
        $this->instruction = $instruction;

        return $this;
    }
}
