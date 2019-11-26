<?php

namespace AppBundle\Indicators\Input;

use AppBundle\Entity\Indicator;
use AppBundle\Indicators\AbstractIndicator;

class LifetimeOfBulbInHours extends AbstractIndicator implements InputIndicatorInterface
{
    use Validation\IsGreaterThanZero;
    use Validation\IsGreaterOrLessOnFivePercent;

    const id = Indicator::INDICATOR_LIFETIME_OF_BULB_IN_HOURS;

    /**
     * LifetimeOfBulbInHours constructor.
     */
    public function __construct()
    {
        // TODO: Must support rename.
        $this->name = 'Lifetime of a bulb in hours';
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
