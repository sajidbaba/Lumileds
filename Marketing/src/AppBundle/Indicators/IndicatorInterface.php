<?php

namespace AppBundle\Indicators;

use AppBundle\Entity\Cell;

interface IndicatorInterface
{
    /**
     * Returns indicator unique id.
     *
     * @return int
     *   Indicator id.
     */
    public function getId(): int;

    /**
     * Number of decimal digits shown
     *
     * @return int
     */
    public function getPrecision(): int;

    /**
     * Is input shown as editable
     *
     * @param Cell $cell
     *
     * @return bool
     */
    public function isEditable(Cell $cell): bool;
}
