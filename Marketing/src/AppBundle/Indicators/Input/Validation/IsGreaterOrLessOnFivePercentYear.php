<?php

namespace AppBundle\Indicators\Input\Validation;

use AppBundle\Entity\Cell;

trait IsGreaterOrLessOnFivePercentYear
{
    use IsGreaterOrLessOnFivePercent {
        variationIsValid as parentIsValid;
    }

    /**
     * Validate if value doesn't differ more that on 5 percent
     * Only for till current year
     *
     * @param Cell $cell
     * @param Cell|null $previousCell
     *
     * @return bool
     */
    public function variationIsValid(Cell $cell, ?Cell $previousCell): bool
    {
        $year = date('Y');

        if ($cell->getYear() >= $year) {
            return true;
        }

        return self::parentIsValid($cell, $previousCell);
    }

    /**
     * @return string
     */
    public function getVariationErrorMessage(): string
    {
        return 'validation.cell.big_variation';
    }
}
