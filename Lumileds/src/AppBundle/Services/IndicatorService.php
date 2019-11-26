<?php

namespace AppBundle\Services;

use AppBundle\Indicators\IndicatorInterface;

class IndicatorService
{
    /**
     * @var array
     */
    private $indicatorRegistry = [];

    /**
     * Add indicator as known.
     *
     * @param IndicatorInterface $class
     *   Indicator object.
     */
    public function addIndicator(IndicatorInterface $class)
    {
        $this->indicatorRegistry[$class->getId()] = $class;
    }

    /**
     * Returns a registry of known indicators.
     *
     * @return array
     *   A set of know indicators.
     */
    public function getRegistry(): array
    {
        return $this->indicatorRegistry;
    }

    /**
     * Fetches an indicator based on it's unique id.
     *
     * @param int $id
     *   Indicator id.
     *
     * @return IndicatorInterface|null
     *   Indicator object if known, null otherwise.
     */
    public function getIndicatorById($id): IndicatorInterface
    {
        return $this->indicatorRegistry[$id];
    }
}
