<?php

namespace AppBundle\Model\Contribution;

use AppBundle\Model\Cell;
use AppBundle\Model\Row;
use AppBundle\Model\Table;

class OneYearTable extends Table
{
    /**
     * {@inheritdoc}
     */
    public function addCell(Cell $cell)
    {
        $this->addYear($cell->getTechnology());

        $row = $this->getRow($cell);
        $row->addCell($cell);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRow(Cell $cell): Row
    {
        $row = (new OneYearRow())
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
}
