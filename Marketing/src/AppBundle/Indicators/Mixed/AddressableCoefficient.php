<?php

namespace AppBundle\Indicators\Mixed;

use AppBundle\Entity\Cell;
use AppBundle\Entity\Indicator;
use AppBundle\Entity\Segment;
use AppBundle\Entity\Technology;
use AppBundle\Indicators\Input\InputIndicatorInterface;
use AppBundle\Indicators\Input\Validation;
use AppBundle\Indicators\AbstractIndicator;
use AppBundle\Indicators\Output\OutputIndicatorInterface;
use AppBundle\Indicators\PercentageIndicatorInterface;
use AppBundle\Repository\CellRepository;
use Doctrine\Common\Persistence\ObjectManager;

class AddressableCoefficient extends AbstractIndicator implements InputIndicatorInterface, OutputIndicatorInterface, PercentageIndicatorInterface
{
    use Validation\Percentage;
    use Validation\IsGreaterOrLessOnFivePercent;

    const id = Indicator::INDICATOR_ADDR_COEFF;
    const YEAR_OFFSET = 5;

    /**
     * AddressableCoefficient constructor.
     */
    public function __construct()
    {
        $this->name = 'Addressable Coefficient';
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
            Indicator::INDICATOR_OPERATION_RATE,
            Indicator::INDICATOR_MARKET_VOLUME,
            Indicator::INDICATOR_ADDR_COEFF,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function isEditable(Cell $cell): bool
    {
        $isLedRf = $this->isLedRf($cell);
        $isOffsetYear = $cell->getYear() > Indicator::START_YEAR + self::YEAR_OFFSET;
        $isHV = $cell->getSegment()->getId() == Segment::SEGMENT_HV;

        if ($isHV) {
            return false;
        } else {
            return !($isLedRf && $isOffsetYear);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCalculatedValue(Cell $cell, ObjectManager $em, $isUpload)
    {
        if ($this->isEditable($cell)) {
            return $cell->getValue();
        }

        if ($cell->getSegment()->getId() == Segment::SEGMENT_HV) {
            return 1.0;
        }

        $isSlLedRf = $cell->getTechnology()->getId() == Technology::TECHNOLOGY_SL_LED_RF;
        $isHlLedRf = $cell->getTechnology()->getId() == Technology::TECHNOLOGY_HL_LED_RF;
        if (($isSlLedRf || $isHlLedRf) && $cell->getYear() == date('Y') + 2) {
            $endYear1 = Indicator::START_YEAR;
            $endYear2 = Indicator::START_YEAR + 5;
        } else {
            $endYear1 = $cell->getYear() - 6;
            $endYear2 = $cell->getYear() - 1;
        }

        $yearsRange1 = range(Indicator::START_YEAR, $endYear1);
        $yearsRange2 = range(Indicator::START_YEAR, $endYear2);

        $sumOne = 0;
        $sumTwo = 0;

        /** @var CellRepository $cellRepository */
        $cellRepository = $em->getRepository(Cell::class);

        foreach ($yearsRange1 as $year) {
            /** @var Cell $marketVolume */
            $marketVolume1 = $cellRepository->findOneByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                Indicator::INDICATOR_MARKET_VOLUME,
                $cell->getTechnology(),
                $year
            );

            if ($marketVolume1) {
                $sumOne += (float) $marketVolume1->getValue();
            }
        }

        foreach ($yearsRange2 as $year) {
           /** @var Cell $marketVolume */
            $marketVolume2 = $cellRepository->findOneByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                Indicator::INDICATOR_MARKET_VOLUME,
                $cell->getTechnology(),
                $year
            );

            if ($marketVolume2) {
                $sumTwo += (float) $marketVolume2->getValue();
            }
        }

        if ($sumOne && $sumTwo) {
            return $sumOne / $sumTwo;
        }

        return 0;
    }

    /**
     * @param Cell $cell
     *
     * @return bool
     */
    private function isLedRf(Cell $cell): bool
    {
        $isLedRf = in_array(
            $cell->getTechnology()->getId(),
            [
                Technology::TECHNOLOGY_HL_LED_RF,
                Technology::TECHNOLOGY_SL_LED_RF,
            ],
            true
        );

        return $isLedRf;
    }
}
