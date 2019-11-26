<?php

namespace AppBundle\Indicators\Input;

use AppBundle\Entity\Indicator;
use AppBundle\Indicators\AbstractIndicator;

class Parc extends AbstractIndicator implements InputIndicatorInterface
{
    use Validation\IsGreaterThanZero;
    use Validation\IsGreaterOrLessOnFivePercent;

    const id = Indicator::INDICATOR_PARC;

    /**
     * Parc constructor.
     */
    public function __construct()
    {
        $this->name = 'Parc';
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return self::id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAffectedIndicators(): array
    {
        return [
            Indicator::INDICATOR_MARKET_VOLUME,
        ];
    }
}
