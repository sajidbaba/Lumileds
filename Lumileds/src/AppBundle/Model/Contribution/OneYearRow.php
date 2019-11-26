<?php

namespace AppBundle\Model\Contribution;

use AppBundle\Model\Row;

class OneYearRow extends Row
{
    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return implode(
            '-',
            [
                $this->getMarket(),
                $this->getSegment(),
                $this->getIndicator(),
            ]
        );
    }
}
