<?php

namespace AppBundle\Indicators\Mixed;

use AppBundle\Entity\Cell;
use AppBundle\Entity\Indicator;
use AppBundle\Entity\Technology;
use AppBundle\Indicators\AbstractIndicator;
use AppBundle\Indicators\Input\InputIndicatorInterface;
use AppBundle\Indicators\Output\OutputIndicatorInterface;
use AppBundle\Indicators\PercentageIndicatorInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Indicators\Input\Validation;

class UpgradeTakeRate extends AbstractIndicator implements InputIndicatorInterface, OutputIndicatorInterface, PercentageIndicatorInterface
{
    use Validation\Percentage;
    use Validation\IsGreaterOrLessOnFivePercent;

    const id = Indicator::INDICATOR_UPGRADE_TAKE_RATE;

    /**
     * UpgradeTakeRate constructor.
     */
    public function __construct()
    {
        $this->name = 'Upgrade Take Rate';
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
        return [
            Indicator::INDICATOR_UPGRADE_TAKE_RATE,
            Indicator::INDICATOR_OPERATION_RATE,
            Indicator::INDICATOR_TECH_SPLIT,
            Indicator::INDICATOR_MARKET_VOLUME,
            Indicator::INDICATOR_ADDR_COEFF,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function isEditable(Cell $cell): bool
    {
        $technologyId = $cell->getTechnology() ? $cell->getTechnology()->getId() : null;

        return !in_array(
            $technologyId,
            [
                Technology::TECHNOLOGY_SL_CHMSL_LED_RF,
                Technology::TECHNOLOGY_SL_DRL_LED_RF,
                Technology::TECHNOLOGY_SL_POSL_LED_RF,
                Technology::TECHNOLOGY_SL_STOP_LED_RF,
                Technology::TECHNOLOGY_SL_TURN_LED_RF,
                Technology::TECHNOLOGY_SL_LP_LED_RF,
                Technology::TECHNOLOGY_SL_FF_LED_RF,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCalculatedValue(Cell $cell, ObjectManager $em, $isUpload)
    {
        $technologyId = $cell->getTechnology() ? $cell->getTechnology()->getId() : null;

        if (in_array(
            $technologyId,
            [
                Technology::TECHNOLOGY_SL_CHMSL_LED_RF,
                Technology::TECHNOLOGY_SL_DRL_LED_RF,
                Technology::TECHNOLOGY_SL_POSL_LED_RF,
                Technology::TECHNOLOGY_SL_STOP_LED_RF,
                Technology::TECHNOLOGY_SL_TURN_LED_RF,
                Technology::TECHNOLOGY_SL_LP_LED_RF,
                Technology::TECHNOLOGY_SL_FF_LED_RF,
            ]
        )) {
            $dependencies = [
                [
                    'indicator' => Indicator::INDICATOR_UPGRADE_TAKE_RATE,
                    'technology' => Technology::TECHNOLOGY_SL_LED_RF,
                ]
            ];
            parent::resolveDependencies($dependencies, $cell, $em, false, $isUpload);
            
            $upgradeTakeRateSlLedRf = $em->getRepository(Cell::class)
                ->findOneByIndicatorTechnologyAndYear(
                    $cell->getCountry(),
                    $cell->getSegment(),
                    Indicator::INDICATOR_UPGRADE_TAKE_RATE,
                    Technology::TECHNOLOGY_SL_LED_RF,
                    $cell->getYear()
                );

            return $upgradeTakeRateSlLedRf->getValue();
        }

        return $cell->getValue();
    }
}
