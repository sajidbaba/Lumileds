<?php

namespace AppBundle\Indicators\Input;

use AppBundle\Entity\Indicator;
use AppBundle\Indicators\AbstractIndicator;
use AppBundle\Indicators\Input\Validation\IsGreaterThanZero;

class ExchangeRate extends AbstractIndicator implements InputIndicatorInterface
{
    use IsGreaterThanZero;
    use Validation\IsGreaterOrLessOnFivePercent;

    const id = Indicator::INDICATOR_EXCHANGE_RATE;

    /**
     * ExchangeRate constructor.
     */
    public function __construct()
    {
        $this->name = 'Exchange Rate';
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
        return 4;
    }

    /**
     * {@inheritdoc}
     */
    public function getAffectedIndicators(): array
    {
        return [
            Indicator::INDICATOR_ASP_USD,
        ];
    }
}
