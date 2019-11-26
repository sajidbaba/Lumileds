<?php

namespace AppBundle\Indicators\Input;

use AppBundle\Entity\Indicator;
use AppBundle\Indicators\AbstractIndicator;

class AverageSpeedInKmH extends AbstractIndicator implements InputIndicatorInterface
{
    use Validation\IsGreaterThanZero;
    use Validation\IsGreaterOrLessOnFivePercent;

    const id = Indicator::INDICATOR_AVERAGE_SPEED_IN_KM_H;

    /**
     * AverageSpeedInKmH constructor.
     */
    public function __construct()
    {
        $this->name = 'Average Speed in km/h';
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
            Indicator::INDICATOR_LIFETIME_OF_BULB_IN_YEARS,
            Indicator::INDICATOR_OPERATION_RATE,
        ];
    }
}
