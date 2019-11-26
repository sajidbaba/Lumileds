<?php

namespace AppBundle\Indicators\Input;

use AppBundle\Entity\Cell;

interface InputIndicatorInterface
{
    /**
     * Validates the input value.
     *
     * @param Cell $cell
     *   Value being validated.
     *
     * @return bool
     *   true whether is valid, false otherwise.
     */
    public function isValid(Cell $cell): bool;

    /**
     * Validates the variation between current and previous cells.
     *
     * @param Cell $cell
     *   Value being validated.
     * @param Cell $previousCell
     *  Value for compare
     *
     * @return bool
     *   true whether is valid, false otherwise.
     */
    public function variationIsValid(Cell $cell, ?Cell $previousCell): bool;

    /**
     * Returns message string in case validation failed
     *
     * @return string
     */
    public function getErrorMessage(): string;

    /**
     * Returns message string in case validation failed
     *
     * @return string
     */
    public function getVariationErrorMessage(): string;

    /**
     * Returns a set of indicators that must be re-calculated
     * when this indicator value is changed.
     *
     * @return array
     *   A set of dependant indicators.
     */
    public function getAffectedIndicators(): array;
}
