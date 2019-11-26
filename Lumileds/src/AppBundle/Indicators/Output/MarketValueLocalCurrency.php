<?php

namespace AppBundle\Indicators\Output;

use AppBundle\Entity\Cell;
use AppBundle\Entity\Indicator;
use AppBundle\Entity\Technology;
use AppBundle\Repository\CellRepository;
use Doctrine\Common\Persistence\ObjectManager;

class MarketValueLocalCurrency extends AbstractOutputIndicator
{
    const id = Indicator::INDICATOR_MARKET_VALUE_LC;

    /**
     * MarketValueLocalCurrency constructor.
     */
    public function __construct()
    {
        $this->name = 'Market Value LC';
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
    public function getCalculatedValue(Cell $cell, ObjectManager $em, $isUpload)
    {
        /** @var CellRepository $cellRepository */
        $cellRepository = $em->getRepository(Cell::class);

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
                Technology::TECHNOLOGY_SL_HIPER,
            ];

            /** @var Cell $marketValueLc */
            $marketValues = $cellRepository->findByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                [Indicator::INDICATOR_MARKET_VALUE_LC],
                $technologies,
                $cell->getYear()
            );

            $value = 0;
            foreach ($marketValues as $marketValue) {
                if ($marketValue) {
                    $value += (float) $marketValue->getValue();
                }
            }
        }
        else {
            $cellTechnology = $cell->getTechnology()->getId();
            /** @var Cell $marketVolume */
            $marketVolume = $cellRepository->findOneByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                Indicator::INDICATOR_MARKET_VOLUME,
                $cellTechnology,
                $cell->getYear()
            );

            /** @var Cell $aspLc */
            $aspLc = $cellRepository->findOneByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                Indicator::INDICATOR_ASP_LC,
                $cellTechnology,
                $cell->getYear()
            );

            if ($marketVolume && $aspLc) {
                $value = $marketVolume->getValue() * $aspLc->getValue();
            }
        }

        return $value;
    }
}
