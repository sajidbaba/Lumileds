<?php

namespace AppBundle\Indicators\Input\Validation;

use AppBundle\Entity\Cell;

trait PercentageGreaterThanZeroYear
{
    /**
     * Validate if value is Percentage and > 0
     * Only for till current year
     *
     * @param Cell $cell
     *
     * @return bool
     */
    public function isValid(Cell $cell): bool
    {
        $value = $cell->getValue();
        $year = (new \DateTime())->format('Y');

        if ($cell->getYear() >= $year) {
            return true;
        }

        return is_numeric($value) && $value > 0;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return 'validation.cell.percentage_greater_than_zero';
    }
}
