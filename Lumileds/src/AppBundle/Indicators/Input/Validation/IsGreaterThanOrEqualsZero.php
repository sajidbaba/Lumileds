<?php

namespace AppBundle\Indicators\Input\Validation;

use AppBundle\Entity\Cell;

trait IsGreaterThanOrEqualsZero
{
    /**
     * Validate if value is >= 0
     *
     * @param Cell $cell
     *
     * @return bool
     */
    public function isValid(Cell $cell): bool
    {
        $value = $cell->getValue();
        return is_numeric($value) && $value >= 0;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return 'validation.cell.is_greater_than_or_equals_zero';
    }
}
