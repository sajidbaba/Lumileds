<?php

namespace AppBundle\Indicators\Input;

use AppBundle\Entity\Indicator;
use AppBundle\Indicators\AbstractIndicator;

class LumiledsSalesUSD extends AbstractIndicator implements InputIndicatorInterface
{
    use Validation\IsGreaterThanZero;
    use Validation\IsGreaterOrLessOnFivePercent;

    const id = Indicator::INDICATOR_LL_SALES_USD;

    /**
     * LumiledsSalesUSD constructor.
     */
    public function __construct()
    {
        $this->name = 'Lumileds Sales USD';
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
        return [];
    }
}
