<?php

namespace AppBundle\Indicators\Input\Validation;

use AppBundle\Entity\Cell;

trait PercentageYear
{
    /**
     * Validate if value is Percentage
     * >= 0%  and < 100%
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

        if ($cell->getYear() > $year) {
            return true;
        }

        return is_numeric($value) && $value >= 0 && $value <= 1;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return 'validation.cell.percentage';
    }
}
