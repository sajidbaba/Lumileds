<?php

namespace AppBundle\Indicators\Input;

use AppBundle\Entity\Indicator;
use AppBundle\Indicators\AbstractIndicator;

class HoursUsagePerThousandKm extends AbstractIndicator implements InputIndicatorInterface
{
    use Validation\IsGreaterThanZero;
    use Validation\IsGreaterOrLessOnFivePercent;

    const id = Indicator::INDICATOR_HOURS_USAGE_PER_THOUSAND_KM;

    /**
     * HoursUsagePerThousandKm constructor.
     */
    public function __construct()
    {
        $this->name = 'Hours of Usage per 1000 km';
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
    public function getPrecision(): int
    {
        return 2;
    }

    /**
     * {@inheritdoc}
     */
    public function getAffectedIndicators(): array
    {
        return [];
    }
}
