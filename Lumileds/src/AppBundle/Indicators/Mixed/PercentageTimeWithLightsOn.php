<?php

namespace AppBundle\Indicators\Mixed;

use AppBundle\Entity\Cell;
use AppBundle\Entity\Indicator;
use AppBundle\Indicators\AbstractIndicator;
use AppBundle\Indicators\Input\InputIndicatorInterface;
use AppBundle\Indicators\Input\Validation\IsGreaterOrLessOnFivePercent;
use AppBundle\Indicators\Input\Validation\PercentageGreaterThanZero;
use AppBundle\Indicators\Output\OutputIndicatorInterface;
use AppBundle\Indicators\PercentageIndicatorInterface;
use AppBundle\Repository\CellRepository;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class PercentageTimeWithLightsOn
 *
 * [1 - TimeLightsOn]
 */
class PercentageTimeWithLightsOn extends AbstractIndicator implements InputIndicatorInterface, OutputIndicatorInterface, PercentageIndicatorInterface
{
    use PercentageGreaterThanZero;
    use IsGreaterOrLessOnFivePercent;

    const id = Indicator::INDICATOR_PERCENTAGE_TIME_WITH_LIGHTS_ON;

    /**
     * PercentageTimeWithLightsOn constructor.
     */
    public function __construct()
    {
        $this->name = '% time with lights on';
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
            Indicator::INDICATOR_PERCENTAGE_TIME_WITH_LIGHTS_ON,
            Indicator::INDICATOR_OPERATION_RATE,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCalculatedValue(Cell $cell, ObjectManager $em, $isUpload)
    {
        if (!$cell->getTechnology()) {
            return $cell->getValue();
        }

        /** @var CellRepository $cellRepository */
        $cellRepository = $em->getRepository(Cell::class);

        /** @var Cell $timeLightsOn */
        $timeLightsOn = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_PERCENTAGE_TIME_WITH_LIGHTS_ON,
            null,
            $cell->getYear()
        );

        $value = null;
        if ($timeLightsOn) {
            $value = 1 - $timeLightsOn->getValue();
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function isEditable(Cell $cell): bool
    {
        return !$cell->getTechnology();
    }
}
