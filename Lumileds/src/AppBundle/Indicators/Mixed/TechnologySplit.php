<?php

namespace AppBundle\Indicators\Mixed;

use AppBundle\Entity\Cell;
use AppBundle\Entity\Indicator;
use AppBundle\Entity\Segment;
use AppBundle\Entity\Technology;
use AppBundle\Indicators\AbstractIndicator;
use AppBundle\Indicators\Input\InputIndicatorInterface;
use AppBundle\Indicators\Input\Validation\IsGreaterOrLessOnFivePercent;
use AppBundle\Indicators\Input\Validation\Percentage;
use AppBundle\Indicators\Output\OutputIndicatorInterface;
use AppBundle\Indicators\PercentageIndicatorInterface;
use AppBundle\Repository\CellRepository;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class TechnologySplit
 */
class TechnologySplit extends AbstractIndicator implements InputIndicatorInterface, OutputIndicatorInterface, PercentageIndicatorInterface
{
    use Percentage;
    use IsGreaterOrLessOnFivePercent;

    const id = Indicator::INDICATOR_TECH_SPLIT;

    /**
     * TechnologySplit constructor.
     */
    public function __construct()
    {
        $this->name = 'Technology Split';
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
            Indicator::INDICATOR_TECH_SPLIT,
            Indicator::INDICATOR_OPERATION_RATE,
        ];
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
    public function isEditable(Cell $cell): bool
    {
        $technologyId = $cell->getTechnology() ? $cell->getTechnology()->getId() : null;

        return in_array(
            $technologyId,
            [
                Technology::TECHNOLOGY_HL_XENON,
                Technology::TECHNOLOGY_HL_NON_HALOGEN,
                Technology::TECHNOLOGY_HL_LED,
                Technology::TECHNOLOGY_SL_POSL_LED,
            ],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCalculatedValue(Cell $cell, ObjectManager $em, $isUpload)
    {
        /** @var CellRepository $cellRepository */
        $cellRepository = $em->getRepository(Cell::class);

        $technologyId = $cell->getTechnology()->getId();

        $isHalogen = $technologyId === Technology::TECHNOLOGY_HL_HALOGEN;
        $isConventional = in_array(
            $technologyId,
            [
                Technology::TECHNOLOGY_SL_DRL_CONV,
                Technology::TECHNOLOGY_SL_POSL_CONV,
                Technology::TECHNOLOGY_SL_TURN_CONV,
                Technology::TECHNOLOGY_SL_STOP_CONV,
                Technology::TECHNOLOGY_SL_CHMSL_CONV,
                Technology::TECHNOLOGY_SL_LP_CONV,
                Technology::TECHNOLOGY_SL_FF_CONV,
            ]
        );
        $isLedRf = in_array(
            $technologyId,
            [
                Technology::TECHNOLOGY_HL_LED_RF,
                Technology::TECHNOLOGY_SL_DRL_LED_RF,
                Technology::TECHNOLOGY_SL_POSL_LED_RF,
                Technology::TECHNOLOGY_SL_TURN_LED_RF,
                Technology::TECHNOLOGY_SL_STOP_LED_RF,
                Technology::TECHNOLOGY_SL_CHMSL_LED_RF,
                Technology::TECHNOLOGY_SL_LP_LED_RF,
                Technology::TECHNOLOGY_SL_FF_LED_RF,
            ]
        );
        $isLed = in_array(
            $technologyId,
            [
                Technology::TECHNOLOGY_SL_DRL_LED,
                Technology::TECHNOLOGY_SL_POSL_LED,
                Technology::TECHNOLOGY_SL_TURN_LED,
                Technology::TECHNOLOGY_SL_STOP_LED,
                Technology::TECHNOLOGY_SL_CHMSL_LED,
                Technology::TECHNOLOGY_SL_LP_LED,
                Technology::TECHNOLOGY_SL_FF_LED,
            ]
        );

        if ($isHalogen) {
            $value = $this->calculateHalogen($cell, $cellRepository, $em);
        } elseif ($isConventional) {
            $value = $this->calculateConventional($cell, $em);
        } elseif ($isLedRf) {
            $value = $this->calculateLedRf($cell, $cellRepository);
        } elseif ($isLed) {
            $value = $this->calculateLed($cell, $cellRepository);
        } else {
            $value = $cell->getValue();
        }

        return $value;
    }

    /**
     * @param Cell $cell
     * @param CellRepository $cellRepository
     * @param ObjectManager $em
     *
     * @return string|null
     */
    private function calculateHalogen(Cell $cell, CellRepository $cellRepository, ObjectManager $em): ?string
    {
        $value = null;
        $is2W = $cell->getSegment()->getId() === Segment::SEGMENT_2W;

        $dependencies = [
            [
                'indicator' => Indicator::INDICATOR_TECH_SPLIT,
                'technology' => $is2W ? Technology::TECHNOLOGY_HL_NON_HALOGEN : Technology::TECHNOLOGY_HL_XENON,
            ],
            [
                'indicator' => Indicator::INDICATOR_TECH_SPLIT,
                'technology' => Technology::TECHNOLOGY_HL_LED,
            ],
            [
                'indicator' => Indicator::INDICATOR_TECH_SPLIT,
                'technology' => Technology::TECHNOLOGY_HL_LED_RF,
            ],
        ];

        parent::resolveDependencies($dependencies, $cell, $em, true, false);

        $nonHalogen = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_TECH_SPLIT,
            Technology::TECHNOLOGY_HL_NON_HALOGEN,
            $cell->getYear()
        );

        /** @var Cell $xenon */
        $xenon = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_TECH_SPLIT,
            Technology::TECHNOLOGY_HL_XENON,
            $cell->getYear()
        );

        /** @var Cell $led */
        $led = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_TECH_SPLIT,
            Technology::TECHNOLOGY_HL_LED,
            $cell->getYear()
        );

        /** @var Cell $ledRf */
        $ledRf = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_TECH_SPLIT,
            Technology::TECHNOLOGY_HL_LED_RF,
            $cell->getYear()
        );

        if ($is2W && $nonHalogen && $led && $ledRf) {
            $value = 1 - $nonHalogen->getValue() - $led->getValue() - $ledRf->getValue();
        } elseif ($xenon && $led && $ledRf) {
            $value = 1 - $xenon->getValue() - $led->getValue() - $ledRf->getValue();
        }

        return $value;
    }

    /**
     * @param Cell $cell
     * @param ObjectManager $em
     *
     * @return float|null
     */
    private function calculateConventional(Cell $cell, ObjectManager $em): ?float
    {
        /** @var CellRepository $cellRepository */
        $cellRepository = $em->getRepository(Cell::class);

        $value = null;

        switch ($cell->getTechnology()->getId()) {
            case Technology::TECHNOLOGY_SL_DRL_CONV:
                $refTechnology1 = Technology::TECHNOLOGY_SL_DRL_LED;
                $refTechnology2 = Technology::TECHNOLOGY_SL_DRL_LED_RF;
                break;
            case Technology::TECHNOLOGY_SL_POSL_CONV:
                $refTechnology1 = Technology::TECHNOLOGY_SL_POSL_LED;
                $refTechnology2 = Technology::TECHNOLOGY_SL_POSL_LED_RF;
                break;
            case Technology::TECHNOLOGY_SL_TURN_CONV:
                $refTechnology1 = Technology::TECHNOLOGY_SL_TURN_LED;
                $refTechnology2 = Technology::TECHNOLOGY_SL_TURN_LED_RF;
                break;
            case Technology::TECHNOLOGY_SL_STOP_CONV:
                $refTechnology1 = Technology::TECHNOLOGY_SL_STOP_LED;
                $refTechnology2 = Technology::TECHNOLOGY_SL_STOP_LED_RF;
                break;
            case Technology::TECHNOLOGY_SL_CHMSL_CONV:
                $refTechnology1 = Technology::TECHNOLOGY_SL_CHMSL_LED;
                $refTechnology2 = Technology::TECHNOLOGY_SL_CHMSL_LED_RF;
                break;
            case Technology::TECHNOLOGY_SL_LP_CONV:
                $refTechnology1 = Technology::TECHNOLOGY_SL_LP_LED;
                $refTechnology2 = Technology::TECHNOLOGY_SL_LP_LED_RF;
                break;
            case Technology::TECHNOLOGY_SL_FF_CONV:
                $refTechnology1 = Technology::TECHNOLOGY_SL_FF_LED;
                $refTechnology2 = Technology::TECHNOLOGY_SL_FF_LED_RF;
                break;
            default:
                $refTechnology1 = null;
                $refTechnology2 = null;
        }
        /** @var Cell $led */
        $led = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_TECH_SPLIT,
            $refTechnology1,
            $cell->getYear()
        );

        $dependencies = [
            [
                'indicator' => Indicator::INDICATOR_TECH_SPLIT,
                'technology' => $refTechnology2,
            ],
        ];

        parent::resolveDependencies($dependencies, $cell, $em, true, false);

        /** @var Cell $ledRf */
        $ledRf = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_TECH_SPLIT,
            $refTechnology2,
            $cell->getYear()
        );

        if ($led && $ledRf) {
            $value = 1.0 - (float)$led->getValue() - (float)$ledRf->getValue();
        }

        return $value;
    }

    /**
     * @param Cell $cell
     * @param CellRepository $cellRepository
     *
     * @return float|null
     */
    private function calculateLedRf(Cell $cell, CellRepository $cellRepository): ?float
    {
        $value = null;
        $technology = null;

        $isHlLedRf = $cell->getTechnology()->getId() === Technology::TECHNOLOGY_HL_LED_RF;
        $is2W = $cell->getSegment()->getId() === Segment::SEGMENT_2W;

        if ($cell->getYear() == Indicator::START_YEAR) {
            /** @var Cell $upgradeTakeRate */
            $upgradeTakeRate = $cellRepository->findOneByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                Indicator::INDICATOR_UPGRADE_TAKE_RATE,
                $cell->getTechnology(),
                $cell->getYear()
            );

            switch ($cell->getTechnology()->getId()) {
                case Technology::TECHNOLOGY_HL_LED_RF:
                    $technology = Technology::TECHNOLOGY_HL_LED;
                    break;
                case Technology::TECHNOLOGY_SL_DRL_LED_RF:
                case Technology::TECHNOLOGY_SL_POSL_LED_RF:
                case Technology::TECHNOLOGY_SL_TURN_LED_RF:
                case Technology::TECHNOLOGY_SL_STOP_LED_RF:
                case Technology::TECHNOLOGY_SL_CHMSL_LED_RF:
                case Technology::TECHNOLOGY_SL_LP_LED_RF:
                case Technology::TECHNOLOGY_SL_FF_LED_RF:
                    $technology = Technology::TECHNOLOGY_SL_POSL_LED;
                    break;
            }

            /** @var Cell $techSplit */
            $techSplit = $cellRepository->findOneByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                Indicator::INDICATOR_TECH_SPLIT,
                $technology,
                $cell->getYear()
            );

            if ($isHlLedRf) {
                $techSplit2Technology = null;
                if ($is2W) {
                    $techSplit2Technology = Technology::TECHNOLOGY_HL_NON_HALOGEN;
                } else {
                    $techSplit2Technology = Technology::TECHNOLOGY_HL_XENON;
                }

                /** @var Cell $techSplit2 */
                $techSplit2 = $cellRepository->findOneByIndicatorTechnologyAndYear(
                    $cell->getCountry(),
                    $cell->getSegment(),
                    Indicator::INDICATOR_TECH_SPLIT,
                    $techSplit2Technology,
                    $cell->getYear()
                );

                if ($upgradeTakeRate && $techSplit && $techSplit2) {
                    $value = (float) $upgradeTakeRate->getValue() * (1.0 - (float) $techSplit->getValue() - (float) $techSplit2->getValue());
                }
            } else {
                if ($upgradeTakeRate && $techSplit) {
                    $value = (float) $upgradeTakeRate->getValue() * (1.0 - (float) $techSplit->getValue());
                }
            }
        } else {
            switch ($cell->getTechnology()->getId()) {
                case Technology::TECHNOLOGY_HL_LED_RF:
                    $technology = Technology::TECHNOLOGY_HL_HALOGEN;
                    break;
                case Technology::TECHNOLOGY_SL_DRL_LED_RF:
                    $technology = Technology::TECHNOLOGY_SL_DRL_CONV;
                    break;
                case Technology::TECHNOLOGY_SL_POSL_LED_RF:
                    $technology = Technology::TECHNOLOGY_SL_POSL_CONV;
                    break;
                case Technology::TECHNOLOGY_SL_TURN_LED_RF:
                    $technology = Technology::TECHNOLOGY_SL_TURN_CONV;
                    break;
                case Technology::TECHNOLOGY_SL_STOP_LED_RF:
                    $technology = Technology::TECHNOLOGY_SL_STOP_CONV;
                    break;
                case Technology::TECHNOLOGY_SL_CHMSL_LED_RF:
                    $technology = Technology::TECHNOLOGY_SL_CHMSL_CONV;
                    break;
                case Technology::TECHNOLOGY_SL_LP_LED_RF:
                    $technology = Technology::TECHNOLOGY_SL_LP_CONV;
                    break;
                case Technology::TECHNOLOGY_SL_FF_LED_RF:
                    $technology = Technology::TECHNOLOGY_SL_FF_CONV;
                    break;
            }
            /** @var Cell $techSplitPreviousYear */
            $techSplitPreviousYear = $cellRepository->findOneByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                Indicator::INDICATOR_TECH_SPLIT,
                $cell->getTechnology(),
                $cell->getYear() - 1
            );

            /** @var Cell $techSplitConvPreviousYear */
            $techSplitConvPreviousYear = $cellRepository->findOneByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                Indicator::INDICATOR_TECH_SPLIT,
                $technology,
                $cell->getYear() - 1
            );

            /** @var Cell $upgradeTakeRate */
            $upgradeTakeRate = $cellRepository->findOneByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                Indicator::INDICATOR_UPGRADE_TAKE_RATE,
                $cell->getTechnology(),
                $cell->getYear()
            );

            if ($techSplitPreviousYear && $techSplitConvPreviousYear && $upgradeTakeRate) {
                $value = (float) $techSplitPreviousYear->getValue() + (float) $upgradeTakeRate->getValue() * (float) $techSplitConvPreviousYear->getValue();
            }
        }

        return $value;
    }
    /**
     * @param Cell $cell
     * @param CellRepository $cellRepository
     *
     * @return float|null
     */
    private function calculateLed(Cell $cell, CellRepository $cellRepository): ?float
    {
        /** @var CellRepository $cellRepository */
        $techSplitSLLed = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_TECH_SPLIT,
            Technology::TECHNOLOGY_SL_POSL_LED,
            $cell->getYear()
        );
        
        return $techSplitSLLed->getValue();
    }
}
