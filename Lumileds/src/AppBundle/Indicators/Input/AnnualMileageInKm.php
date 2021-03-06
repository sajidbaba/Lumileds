<?php

namespace AppBundle\Indicators\Input;

use AppBundle\Entity\Indicator;
use AppBundle\Indicators\AbstractIndicator;

class AnnualMileageInKm extends AbstractIndicator implements InputIndicatorInterface
{
    use Validation\IsGreaterThanZero;
    use Validation\IsGreaterOrLessOnFivePercent;

    const id = Indicator::INDICATOR_ANNUAL_MILEAGE_IN_KM;

    /**
     * AnnualMileageInKm constructor.
     */
    public function __construct()
    {
        $this->name = 'Annual Mileage in km';
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
