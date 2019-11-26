<?php

namespace AppBundle\Indicators\Mixed;

use AppBundle\Entity\Cell;
use AppBundle\Entity\Indicator;
use AppBundle\Entity\Technology;
use AppBundle\Indicators\AbstractIndicator;
use AppBundle\Indicators\Input\Validation;
use AppBundle\Indicators\Input\InputIndicatorInterface;
use AppBundle\Indicators\Output\OutputIndicatorInterface;
use AppBundle\Repository\CellRepository;
use Doctrine\Common\Persistence\ObjectManager;

class MarketVolume extends AbstractIndicator implements InputIndicatorInterface, OutputIndicatorInterface
{
    use Validation\IsDecimalGreaterThanZero;
    use Validation\IsGreaterOrLessOnFivePercent;

    const id = Indicator::INDICATOR_MARKET_VOLUME;

    /**
     * MarketVolume constructor.
     */
    public function __construct()
    {
        $this->name = 'Market Volume';
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
            Indicator::INDICATOR_MARKET_VOLUME,
            Indicator::INDICATOR_MARKET_VALUE_USD,
        ];
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

        $value = null;

        switch ($cell->getTechnology()->getId()) {
            case Technology::TECHNOLOGY_HL_HALOGEN:
            case Technology::TECHNOLOGY_HL_NON_HALOGEN:
            case Technology::TECHNOLOGY_HL_XENON:
            case Technology::TECHNOLOGY_HL_LED_RF:
            case Technology::TECHNOLOGY_SL_CONV:
            case Technology::TECHNOLOGY_SL_LED_RF:
                $dependencies = [
                    [
                        'indicator' => Indicator::INDICATOR_OPERATION_RATE,
                        'technology' => $cell->getTechnology()->getId(),
                    ]
                ];
                parent::resolveDependencies($dependencies, $cell, $em, false, $isUpload);

                /** @var Cell $parc */
                $parc = $cellRepository->findOneByIndicatorTechnologyAndYear(
                    $cell->getCountry(),
                    $cell->getSegment(),
                    Indicator::INDICATOR_PARC,
                    null,
                    $cell->getYear()
                );

                /** @var Cell $operationRate */
                $operationRate = $cellRepository->findOneByIndicatorTechnologyAndYear(
                    $cell->getCountry(),
                    $cell->getSegment(),
                    Indicator::INDICATOR_OPERATION_RATE,
                    $cell->getTechnology(),
                    $cell->getYear()
                );

                if ($parc && $operationRate) {
                    $value = round($parc->getValue() * $operationRate->getValue());
                }

                break;
            case Technology::TECHNOLOGY_SL_HIPER:
                $value = $cell->getValue();
                break;
            case Technology::TECHNOLOGY_TOTAL:
                $technologies = [
                    Technology::TECHNOLOGY_HL_HALOGEN,
                    Technology::TECHNOLOGY_HL_NON_HALOGEN,
                    Technology::TECHNOLOGY_HL_XENON,
                    Technology::TECHNOLOGY_HL_LED_RF,
                    Technology::TECHNOLOGY_SL_CONV,
                    Technology::TECHNOLOGY_SL_LED_RF,
                    Technology::TECHNOLOGY_SL_HIPER,
                ];

                /** @var Cell[] $marketVolumes */
                $marketVolumes = $cellRepository->findByIndicatorTechnologyAndYear(
                    $cell->getCountry(),
                    $cell->getSegment(),
                    [Indicator::INDICATOR_MARKET_VOLUME],
                    $technologies,
                    $cell->getYear()
                );

                $value = 0;
                foreach ($marketVolumes as $marketVolume) {
                    if ($marketVolume) {
                        $value += (float) $marketVolume->getValue();
                    }
                }

                break;
        }

        return $value;
    }
}
