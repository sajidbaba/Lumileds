<?php

namespace AppBundle\Indicators\Output;

use AppBundle\Entity\Cell;
use AppBundle\Entity\Indicator;
use AppBundle\Entity\Technology;
use AppBundle\Repository\CellRepository;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Indicators\AbstractIndicator;
use AppBundle\Indicators\Input\Validation;
use AppBundle\Indicators\Input\InputIndicatorInterface;

class MarketValueUSD extends AbstractIndicator implements InputIndicatorInterface, OutputIndicatorInterface
{
    use Validation\IsDecimalGreaterThanZero;
    use Validation\IsGreaterOrLessOnFivePercent;

    const id = Indicator::INDICATOR_MARKET_VALUE_USD;

    /**
     * MarketValueUSD constructor.
     */
    public function __construct()
    {
        $this->name = 'Market Value USD';
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

    /**
     * {@inheritdoc}
     */
    public function isEditable(Cell $cell): bool
    {
        return $cell->getTechnology()->getId() === Technology::TECHNOLOGY_SL_HIPER;
    }

    /**
     * {@inheritdoc}
     */
    public function getCalculatedValue(Cell $cell, ObjectManager $em, $isUpload)
    {
        /** @var CellRepository $cellRepository */
        $cellRepository = $em->getRepository(Cell::class);

        if ($cell->getTechnology()->getId() === Technology::TECHNOLOGY_SL_HIPER){
            return $cell->getValue();
        }

        $value = null;

        $isTotal = $cell->getTechnology()->getId() == Technology::TECHNOLOGY_TOTAL;
        if ($isTotal) {
            $technologies = [
                Technology::TECHNOLOGY_HL_HALOGEN,
                Technology::TECHNOLOGY_HL_NON_HALOGEN,
                Technology::TECHNOLOGY_HL_XENON,
                Technology::TECHNOLOGY_HL_LED_RF,
                Technology::TECHNOLOGY_SL_CONV,
                Technology::TECHNOLOGY_SL_LED_RF,
            ];

            $marketValues = $cellRepository->findByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                [Indicator::INDICATOR_MARKET_VALUE_USD],
                $technologies,
                $cell->getYear()
            );

            $value = 0;
            foreach ($marketValues as $marketValue) {
                if ($marketValue) {
                    $value += (float) $marketValue->getValue();
                }
            }
        } else {
            $cellTechnology = $cell->getTechnology()->getId();

            /** @var Cell $marketVolume */
            $marketVolume = $cellRepository->findOneByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                Indicator::INDICATOR_MARKET_VOLUME,
                $cellTechnology,
                $cell->getYear()
            );

            /** @var Cell $aspUsd */
            $aspUsd = $cellRepository->findOneByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                Indicator::INDICATOR_ASP_USD,
                $cellTechnology,
                $cell->getYear()
            );

            if ($marketVolume && $aspUsd) {
                $value = $marketVolume->getValue() * $aspUsd->getValue();
            }
        }

        return $value;
    }
}
