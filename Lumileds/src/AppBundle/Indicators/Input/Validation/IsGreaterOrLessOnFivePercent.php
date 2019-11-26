<?php

namespace AppBundle\Indicators\Input\Validation;

use AppBundle\Entity\Cell;

trait IsGreaterOrLessOnFivePercent
{
    /**
     * Validate if value doesn't differ more that on 5 percent
     *
     * @param Cell $cell
     * @param Cell|null $previousCell
     *
     * @return bool
     */
    public function variationIsValid(Cell $cell, ?Cell $previousCell): bool
    {
        $maxVariation = (float) $cell->getValue() * 5 / 100;

        if ($previousCell) {
            $variation = (float) abs($previousCell->getValue() - $cell->getValue());

            if ($maxVariation === 0.0 && $variation === 0.0) {
                return true;
            }

            return $variation < $maxVariation;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getVariationErrorMessage(): string
    {
        return 'validation.cell.big_variation';
    }
}
