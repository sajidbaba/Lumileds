<?php

namespace AppBundle\Indicators\Output;

use AppBundle\Entity\Cell;
use AppBundle\Entity\Indicator;
use AppBundle\Entity\Segment;
use AppBundle\Entity\Technology;
use AppBundle\Repository\CellRepository;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class OperationRate
 */
class OperationRate extends AbstractOutputIndicator
{
    const id = Indicator::INDICATOR_OPERATION_RATE;

    /**
     * OperationRate constructor.
     */
    public function __construct()
    {
        $this->name = 'Operation Rate';
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
    public function getCalculatedValue(Cell $cell, ObjectManager $em, $isUpload)
    {
        /** @var CellRepository $cellRepository */
        $cellRepository = $em->getRepository(Cell::class);

        $segmentId = $cell->getSegment()->getId();
        $technologyId = $cell->getTechnology()->getId();

        $is2W = $segmentId === Segment::SEGMENT_2W;
        $isSlLp = $technologyId === Technology::TECHNOLOGY_SL_LP;
        $isSlConv = $technologyId === Technology::TECHNOLOGY_SL_CONV;
        $isSlLedRf = $technologyId === Technology::TECHNOLOGY_SL_LED_RF;
        $isHlXenon = $technologyId === Technology::TECHNOLOGY_HL_XENON;

        $hasSecondAddressableCoefficient = in_array(
            $technologyId,
            [
                Technology::TECHNOLOGY_SL_CHMSL_LED_RF,
                Technology::TECHNOLOGY_SL_DRL_LED_RF,
                Technology::TECHNOLOGY_SL_POSL_LED_RF,
                Technology::TECHNOLOGY_SL_STOP_LED_RF,
                Technology::TECHNOLOGY_SL_TURN_LED_RF,
                Technology::TECHNOLOGY_SL_LP_LED_RF,
                Technology::TECHNOLOGY_SL_TURN_LED_RF,
                Technology::TECHNOLOGY_SL_FF_LED_RF,
            ]
        );

        $value = null;
        $addressableCoefficientTech = $technologyId;
        $addressableCoefficientTech2 = null;
        $techSplitTech1 = $technologyId;
        $techSplitTech2 = $technologyId;
        $upgradeTakeRateTech1 = $technologyId;
        $upgradeTakeRateTech2 = $technologyId;
        $lifetimeOfBulbInYearTech = $technologyId;
        $numberOfBulbs = null;

        switch ($technologyId) {
            case Technology::TECHNOLOGY_HL_NON_HALOGEN:
                $numberOfBulbs = Indicator::NUMBER_OF_BULBS_CHMSL;
                break;
            case Technology::TECHNOLOGY_HL_HALOGEN:
                $numberOfBulbs = $is2W ? Indicator::NUMBER_OF_BULBS_CHMSL : Indicator::NUMBER_OF_BULBS_CONV;
                $upgradeTakeRateTech1 = Technology::TECHNOLOGY_HL_LED_RF;
                $addressableCoefficientTech = Technology::TECHNOLOGY_HL_HALOGEN;
                $techSplitTech1 = Technology::TECHNOLOGY_HL_HALOGEN;
                $lifetimeOfBulbInYearTech = Technology::TECHNOLOGY_HL_HALOGEN;
                break;
            case Technology::TECHNOLOGY_HL_XENON:
                $numberOfBulbs = $is2W ? Indicator::NUMBER_OF_BULBS_CHMSL : Indicator::NUMBER_OF_BULBS_CONV;
                $addressableCoefficientTech = Technology::TECHNOLOGY_HL_XENON;
                $techSplitTech1 = Technology::TECHNOLOGY_HL_XENON;
                $lifetimeOfBulbInYearTech = Technology::TECHNOLOGY_HL_XENON;
                $upgradeTakeRateTech1 = Technology::TECHNOLOGY_HL_XENON;
                break;
            case Technology::TECHNOLOGY_SL_TURN:
                $addressableCoefficientTech = Technology::TECHNOLOGY_SL_TURN_CONV;
                $techSplitTech1 = Technology::TECHNOLOGY_SL_TURN_CONV;
                $techSplitTech2 = Technology::TECHNOLOGY_SL_TURN_CONV;
                $upgradeTakeRateTech1 = Technology::TECHNOLOGY_SL_TURN_LED_RF;
                $upgradeTakeRateTech2 = Technology::TECHNOLOGY_SL_CONV;
                $lifetimeOfBulbInYearTech = Technology::TECHNOLOGY_SL_TURN;
                $numberOfBulbs = Indicator::NUMBER_OF_BULBS_TURN;
                break;
            case Technology::TECHNOLOGY_SL_POSL:
                $addressableCoefficientTech = Technology::TECHNOLOGY_SL_POSL_CONV;
                $techSplitTech1 = Technology::TECHNOLOGY_SL_POSL_CONV;
                $techSplitTech2 = Technology::TECHNOLOGY_SL_POSL_CONV;
                $upgradeTakeRateTech1 = Technology::TECHNOLOGY_SL_POSL_LED_RF;
                $upgradeTakeRateTech2 = Technology::TECHNOLOGY_SL_CONV;
                $lifetimeOfBulbInYearTech = Technology::TECHNOLOGY_SL_POSL;
                $numberOfBulbs = $is2W ? Indicator::NUMBER_OF_BULBS_POSL : Indicator::NUMBER_OF_BULBS_TURN;
                break;
            case Technology::TECHNOLOGY_SL_STOP:
                $addressableCoefficientTech = Technology::TECHNOLOGY_SL_STOP_CONV;
                $techSplitTech1 = Technology::TECHNOLOGY_SL_STOP_CONV;
                $techSplitTech2 = Technology::TECHNOLOGY_SL_STOP_CONV;
                $upgradeTakeRateTech1 = Technology::TECHNOLOGY_SL_STOP_LED_RF;
                $upgradeTakeRateTech2 = Technology::TECHNOLOGY_SL_CONV;
                $lifetimeOfBulbInYearTech = Technology::TECHNOLOGY_SL_STOP;
                $numberOfBulbs = $is2W ? Indicator::NUMBER_OF_BULBS_CHMSL : Indicator::NUMBER_OF_BULBS_CONV;
                break;
            case Technology::TECHNOLOGY_SL_CHMSL:
                $addressableCoefficientTech = Technology::TECHNOLOGY_SL_CHMSL_CONV;
                $addressableCoefficientTech2 = Technology::TECHNOLOGY_SL_CHMSL_CONV;
                $techSplitTech1 = Technology::TECHNOLOGY_SL_CHMSL_CONV;
                $techSplitTech2 = Technology::TECHNOLOGY_SL_CHMSL_CONV;
                $upgradeTakeRateTech1 = Technology::TECHNOLOGY_SL_CHMSL_LED_RF;
                $upgradeTakeRateTech2 = Technology::TECHNOLOGY_SL_CONV;
                $lifetimeOfBulbInYearTech = Technology::TECHNOLOGY_SL_STOP;
                $numberOfBulbs = Indicator::NUMBER_OF_BULBS_CHMSL;
                break;
            case Technology::TECHNOLOGY_SL_LP:
                $addressableCoefficientTech = Technology::TECHNOLOGY_SL_LP_CONV;
                $techSplitTech1 = Technology::TECHNOLOGY_SL_LP_CONV;
                $techSplitTech2 = Technology::TECHNOLOGY_SL_LP_CONV;
                $upgradeTakeRateTech1 = Technology::TECHNOLOGY_SL_LP_LED_RF;
                $lifetimeOfBulbInYearTech = Technology::TECHNOLOGY_SL_LP;
                $numberOfBulbs = Indicator::NUMBER_OF_BULBS_CHMSL;
                break;
            case Technology::TECHNOLOGY_SL_POSL_LED_RF:
                $addressableCoefficientTech = Technology::TECHNOLOGY_SL_LED_RF;
                $techSplitTech2 = Technology::TECHNOLOGY_SL_POSL_CONV;
                $lifetimeOfBulbInYearTech = Technology::TECHNOLOGY_SL_LED_RF;
                $numberOfBulbs = $is2W ? Indicator::NUMBER_OF_BULBS_POSL : Indicator::NUMBER_OF_BULBS_TURN;
                break;
            case Technology::TECHNOLOGY_SL_TURN_LED_RF:
                $addressableCoefficientTech = Technology::TECHNOLOGY_SL_LED_RF;
                $techSplitTech2 = Technology::TECHNOLOGY_SL_TURN_CONV;
                $lifetimeOfBulbInYearTech = Technology::TECHNOLOGY_SL_LED_RF;
                $numberOfBulbs = Indicator::NUMBER_OF_BULBS_TURN;
                break;
            case Technology::TECHNOLOGY_SL_STOP_LED_RF:
                $addressableCoefficientTech = Technology::TECHNOLOGY_SL_LED_RF;
                $techSplitTech2 = Technology::TECHNOLOGY_SL_STOP_CONV;
                $lifetimeOfBulbInYearTech = Technology::TECHNOLOGY_SL_LED_RF;
                $numberOfBulbs = Indicator::NUMBER_OF_BULBS_CONV;
                break;
            case Technology::TECHNOLOGY_SL_LP_LED_RF:
                $addressableCoefficientTech = Technology::TECHNOLOGY_SL_LED_RF;
                $techSplitTech2 = Technology::TECHNOLOGY_SL_LP_CONV;
                $lifetimeOfBulbInYearTech = Technology::TECHNOLOGY_SL_LED_RF;
                $numberOfBulbs = Indicator::NUMBER_OF_BULBS_CHMSL;
                break;
            case Technology::TECHNOLOGY_SL_FF_LED_RF:
                $addressableCoefficientTech = Technology::TECHNOLOGY_SL_LED_RF;
                $techSplitTech2 = Technology::TECHNOLOGY_SL_FF_CONV;
                $lifetimeOfBulbInYearTech = Technology::TECHNOLOGY_SL_LED_RF;
                $numberOfBulbs = Indicator::NUMBER_OF_BULBS_CONV;
                break;
            case Technology::TECHNOLOGY_HL_LED_RF:
                $techSplitTech2 = Technology::TECHNOLOGY_HL_HALOGEN;
                $numberOfBulbs = $is2W ? Indicator::NUMBER_OF_BULBS_CHMSL : Indicator::NUMBER_OF_BULBS_CONV;
                break;
            case Technology::TECHNOLOGY_SL_DRL_LED_RF:
                $addressableCoefficientTech = Technology::TECHNOLOGY_SL_LED_RF;
                $addressableCoefficientTech2 = Technology::TECHNOLOGY_SL_DRL_CONV;
                $techSplitTech2 = Technology::TECHNOLOGY_SL_DRL_CONV;
                $lifetimeOfBulbInYearTech = Technology::TECHNOLOGY_SL_LED_RF;
                $numberOfBulbs = Indicator::NUMBER_OF_BULBS_CONV;
                break;
            case Technology::TECHNOLOGY_SL_CHMSL_LED_RF:
                $addressableCoefficientTech = Technology::TECHNOLOGY_SL_LED_RF;
                $addressableCoefficientTech2 = Technology::TECHNOLOGY_SL_CHMSL_CONV;
                $techSplitTech2 = Technology::TECHNOLOGY_SL_CHMSL_CONV;
                $lifetimeOfBulbInYearTech = Technology::TECHNOLOGY_SL_LED_RF;
                $numberOfBulbs = Indicator::NUMBER_OF_BULBS_CHMSL;
                break;
            case Technology::TECHNOLOGY_SL_DRL:
                $addressableCoefficientTech = Technology::TECHNOLOGY_SL_DRL_CONV;
                $addressableCoefficientTech2 = Technology::TECHNOLOGY_SL_DRL_CONV;
                $techSplitTech1 = Technology::TECHNOLOGY_SL_DRL_CONV;
                $techSplitTech2 = Technology::TECHNOLOGY_SL_DRL_CONV;
                $upgradeTakeRateTech1 = Technology::TECHNOLOGY_SL_DRL_LED_RF;
                $upgradeTakeRateTech2 = Technology::TECHNOLOGY_SL_CONV;
                $lifetimeOfBulbInYearTech = Technology::TECHNOLOGY_SL_DRL;
                $numberOfBulbs = Indicator::NUMBER_OF_BULBS_CONV;
                break;
        }

        if (!$isSlLedRf && !$isSlConv) {
            $dependencies = [
                [
                    'indicator' => Indicator::INDICATOR_TECH_SPLIT,
                    'technology' => $techSplitTech1,
                ],
                [
                    'indicator' => Indicator::INDICATOR_TECH_SPLIT,
                    'technology' => $techSplitTech2,
                ],
                [
                    'indicator' => Indicator::INDICATOR_LIFETIME_OF_BULB_IN_YEARS,
                    'technology' => $lifetimeOfBulbInYearTech,
                ],
            ];

            parent::resolveDependencies($dependencies, $cell, $em, true, $isUpload);
        }

        $addressableCoefficient = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_ADDR_COEFF,
            $addressableCoefficientTech,
            $cell->getYear()
        );

        $techSplit1 = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_TECH_SPLIT,
            $techSplitTech1,
            $cell->getYear()
        );

        $techSplit2 = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_TECH_SPLIT,
            $techSplitTech2,
            $cell->getYear()
        );

        $upgradeTakeRate1 = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_UPGRADE_TAKE_RATE,
            $upgradeTakeRateTech1,
            $cell->getYear()
        );

        $upgradeTakeRate2 = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_UPGRADE_TAKE_RATE,
            $upgradeTakeRateTech2,
            $cell->getYear()
        );

        $lifetimeOfBulbInYear = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_LIFETIME_OF_BULB_IN_YEARS,
            $lifetimeOfBulbInYearTech,
            $cell->getYear()
        );

        if (
            $technologyId === Technology::TECHNOLOGY_HL_LED_RF ||
            ($hasSecondAddressableCoefficient && $lifetimeOfBulbInYear->getValue() != 0)
        ) {
            $addressableCoefficient2 = $cellRepository->findOneByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                Indicator::INDICATOR_ADDR_COEFF,
                $addressableCoefficientTech2,
                $cell->getYear()
            );

            $value = $this->calculateWithSecondAddressableCoefficient(
                $addressableCoefficient,
                $addressableCoefficient2,
                $techSplit1,
                $techSplit2,
                $lifetimeOfBulbInYear,
                $upgradeTakeRate1,
                $numberOfBulbs
            );
        } elseif ($isSlLp && $lifetimeOfBulbInYear->getValue() != 0) {
            $value = $this->calculateSlLp(
                $upgradeTakeRate1,
                $addressableCoefficient,
                $techSplit1,
                $lifetimeOfBulbInYear,
                $numberOfBulbs
            );
        } elseif ($isSlConv) {
            $value = $this->calculateSlConv($cell, $cellRepository);
        } elseif ($isSlLedRf) {
            $value = $this->calculateSlLedRf($cell, $em);
        } elseif ($isHlXenon) {
            $value = $this->calculateHlXenon(
                $addressableCoefficient,
                $techSplit1,
                $lifetimeOfBulbInYear,
                $upgradeTakeRate1,
                $numberOfBulbs
            );
        } elseif ($addressableCoefficient && $techSplit1 && $techSplit2 && $numberOfBulbs && $lifetimeOfBulbInYear &&
            $upgradeTakeRate1 && $upgradeTakeRate2 && $lifetimeOfBulbInYear->getValue() != 0)
        {
            $value = $this->calculateDefault(
                $addressableCoefficient,
                $techSplit1,
                $lifetimeOfBulbInYear,
                $upgradeTakeRate1,
                $upgradeTakeRate2,
                $numberOfBulbs
            );
        }

        return $value;
    }

    /**
     * Calculate for SL CHMSL LED RF, SL DRL LED RF, SL PosL LED RF, SL Turn LED RF, SL Stop LED RF, SL LP LED RF,SL FF LED RF
     *
     * @param Cell $addressableCoefficient1
     * @param Cell $addressableCoefficient2
     * @param Cell $techSplit1
     * @param Cell $techSplit2
     * @param Cell $lifetimeOfBulbInYear
     * @param Cell $upgradeTakeRate
     * @param float $numberOfBulbs
     *
     * @return float|int
     */
    private function calculateWithSecondAddressableCoefficient(
        Cell $addressableCoefficient1,
        ?Cell $addressableCoefficient2,
        Cell $techSplit1,
        Cell $techSplit2,
        Cell $lifetimeOfBulbInYear,
        Cell $upgradeTakeRate,
        float $numberOfBulbs
    ) {
        $coef2 = $addressableCoefficient2 ? $addressableCoefficient2->getValue() : 1 ;
        $value = ($addressableCoefficient1->getValue() * $techSplit1->getValue() * $numberOfBulbs / $lifetimeOfBulbInYear->getValue())
            + ($coef2 * $techSplit2->getValue() * $upgradeTakeRate->getValue() * $numberOfBulbs);

        return $value;
    }

    /**
     * Calculate for SL LP
     *
     * @param Cell $upgradeTakeRate
     * @param Cell $addressableCoefficient
     * @param Cell $techSplit
     * @param Cell $lifetimeOfBulbInYear
     * @param float $numberOfBulbs
     *
     * @return float
     */
    private function calculateSlLp(
        Cell $upgradeTakeRate,
        Cell $addressableCoefficient,
        Cell $techSplit,
        Cell $lifetimeOfBulbInYear,
        float $numberOfBulbs
    ) {
        return (1 - $upgradeTakeRate->getValue()) * $addressableCoefficient->getValue() * $techSplit->getValue() * $numberOfBulbs / $lifetimeOfBulbInYear->getValue();
    }

    /**
     * Calculate for SL Conventional
     *
     * @param Cell $cell
     * @param CellRepository $cellRepository
     *
     * @return float
     */
    private function calculateSlConv(Cell $cell, CellRepository $cellRepository): float
    {
        $technologies = [
            Technology::TECHNOLOGY_SL_DRL,
            Technology::TECHNOLOGY_SL_POSL,
            Technology::TECHNOLOGY_SL_TURN,
            Technology::TECHNOLOGY_SL_STOP,
            Technology::TECHNOLOGY_SL_CHMSL,
            Technology::TECHNOLOGY_SL_LP,
        ];

        /** @var Cell[] $operationRates */
        $operationRates = $cellRepository->findByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            [Indicator::INDICATOR_OPERATION_RATE],
            $technologies,
            $cell->getYear()
        );

        $value = 0;
        foreach ($operationRates as $operationRate) {
            if ($operationRate) {
                $value += (float) $operationRate->getValue();
            }
        }

        return $value;
    }

    /**
     * Calculate for SL LED RF
     *
     * @param Cell $cell
     * @param ObjectManager $em
     *
     * @return float
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function calculateSlLedRf(Cell $cell, ObjectManager $em): float
    {
        /** @var CellRepository $cellRepository */
        $cellRepository = $em->getRepository(Cell::class);

        $is2W = $cell->getSegment()->getId() === Segment::SEGMENT_2W;

        if ($is2W) {
            $technologies = [
                Technology::TECHNOLOGY_SL_POSL_LED_RF,
                Technology::TECHNOLOGY_SL_TURN_LED_RF,
                Technology::TECHNOLOGY_SL_STOP_LED_RF,
                Technology::TECHNOLOGY_SL_LP_LED_RF,
            ];
        } else {
            $technologies = [
                Technology::TECHNOLOGY_SL_DRL_LED_RF,
                Technology::TECHNOLOGY_SL_POSL_LED_RF,
                Technology::TECHNOLOGY_SL_TURN_LED_RF,
                Technology::TECHNOLOGY_SL_STOP_LED_RF,
                Technology::TECHNOLOGY_SL_CHMSL_LED_RF,
                Technology::TECHNOLOGY_SL_LP_LED_RF,
                Technology::TECHNOLOGY_SL_FF_LED_RF,
            ];
        }

        $value = 0;
        // TODO: Optimize loop.
        foreach ($technologies as $technology) {
            $dependencies = [
                [
                    'indicator' => Indicator::INDICATOR_OPERATION_RATE,
                    'technology' => $technology,
                ]
            ];
            parent::resolveDependencies($dependencies, $cell, $em, false, false);

            /** @var Cell $indicatorEntity */
            $indicatorEntity = $cellRepository->findOneByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                Indicator::INDICATOR_OPERATION_RATE,
                $technology,
                $cell->getYear()
            );

            if ($indicatorEntity) {
                $value += (float)$indicatorEntity->getValue();
            }
        }

        return $value;
    }

    /**
     * Calculate for HL Xenon
     *
     * @param Cell $addressableCoefficient
     * @param Cell $techSplit1
     * @param Cell $lifetimeOfBulbInYear
     * @param Cell $upgradeTakeRate1
     * @param float $numberOfBulbs
     *
     * @return float
     */
    private function calculateHlXenon(
        Cell $addressableCoefficient,
        Cell $techSplit1,
        Cell $lifetimeOfBulbInYear,
        Cell $upgradeTakeRate1,
        float $numberOfBulbs
    ) {
        $value = ($addressableCoefficient->getValue() * $techSplit1->getValue() * $numberOfBulbs / $lifetimeOfBulbInYear->getValue())
                + ($addressableCoefficient->getValue() * $techSplit1->getValue() * $upgradeTakeRate1->getValue() * $numberOfBulbs) *
                (1 - $numberOfBulbs / $lifetimeOfBulbInYear->getValue() / $numberOfBulbs);

        return $value;
    }

    /**
     * Calculate default
     *
     * @param Cell $addressableCoefficient
     * @param Cell $techSplit1
     * @param Cell $lifetimeOfBulbInYear
     * @param Cell $upgradeTakeRate1
     * @param Cell $upgradeTakeRate2
     * @param float $numberOfBulbs
     *
     * @return float
     */
    private function calculateDefault(
        Cell $addressableCoefficient,
        Cell $techSplit1,
        Cell $lifetimeOfBulbInYear,
        Cell $upgradeTakeRate1,
        Cell $upgradeTakeRate2,
        float $numberOfBulbs
    ) {
        $value = (1 - $upgradeTakeRate1->getValue()) * (($addressableCoefficient->getValue() * $techSplit1->getValue() * $numberOfBulbs / $lifetimeOfBulbInYear->getValue())
                + ($addressableCoefficient->getValue() * $techSplit1->getValue() * $upgradeTakeRate2->getValue() * $numberOfBulbs) *
                (1 - $numberOfBulbs / $lifetimeOfBulbInYear->getValue() / $numberOfBulbs));

        return $value;
    }
}
