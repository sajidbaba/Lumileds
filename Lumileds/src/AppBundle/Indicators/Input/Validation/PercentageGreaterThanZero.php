<?php

namespace AppBundle\Indicators\Input\Validation;

use AppBundle\Entity\Cell;

trait PercentageGreaterThanZero
{
    /**
     * Validate if value is Percentage and > 0
     *
     * @param Cell $cell
     *
     * @return bool
     */
    public function isValid(Cell $cell): bool
    {
        $value = $cell->getValue();

        return is_numeric($value) && $value > 0 && $value <= 1;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return 'validation.cell.percentage_greater_than_zero';
    }
}
