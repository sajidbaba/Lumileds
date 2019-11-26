<?php

namespace AppBundle\Indicators\Output;

use AppBundle\Entity\Cell;
use AppBundle\Entity\Indicator;
use AppBundle\Entity\Technology;
use AppBundle\Repository\CellRepository;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LifetimeOfBulbInYears
 *
 * Technology->SL Turn|SL Stop|SL BU|SL Turn LED RF|SL Stop LED RF|SL BU LED RF
 *   [LifetimeOfBulbInHours / (AnnualMileageKm / 1000 * HoursUsageThousandKm)]
 * else
 *   [LifetimeOfBulbInHours / (AnnualMileageKmh / AverageSpeedKmh * PercentTimeWithLightsOn)]
 */
class LifetimeOfBulbInYears extends AbstractOutputIndicator
{
    const id = Indicator::INDICATOR_LIFETIME_OF_BULB_IN_YEARS;

    /**
     * LifetimeOfBulbInYears constructor.
     */
    public function __construct()
    {
        $this->name = 'Lifetime of a bulb in years';
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

        /** @var Cell $lifetimeHours */
        $lifetimeHours = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_LIFETIME_OF_BULB_IN_HOURS,
            $cell->getTechnology(),
            $cell->getYear()
        );

        /** @var Cell $annualMileage */
        $annualMileage = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_ANNUAL_MILEAGE_IN_KM,
            null,
            $cell->getYear()
        );

        $specifics = [
            Technology::TECHNOLOGY_SL_TURN,
            Technology::TECHNOLOGY_SL_STOP,
            Technology::TECHNOLOGY_SL_TURN_LED_RF,
            Technology::TECHNOLOGY_SL_STOP_LED_RF,
            Technology::TECHNOLOGY_SL_BU_LED_RF,
        ];

        $value = null;
        if (in_array($cell->getTechnology()->getId(), $specifics)) {
            /** @var Cell $usagePerThousandKm */
            $usagePerThousandKm = $cellRepository->findOneByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                Indicator::INDICATOR_HOURS_USAGE_PER_THOUSAND_KM,
                $cell->getTechnology(),
                $cell->getYear()
            );

            if ($lifetimeHours && $annualMileage && $usagePerThousandKm) {
                $value = $lifetimeHours->getValue() / ($annualMileage->getValue() / 1000 * $usagePerThousandKm->getValue());
            }
        } else {
            /** @var Cell $averageSpeed */
            $averageSpeed = $cellRepository->findOneByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                Indicator::INDICATOR_AVERAGE_SPEED_IN_KM_H,
                null,
                $cell->getYear()
            );

            $specifics = [
                Technology::TECHNOLOGY_SL_DRL,
                Technology::TECHNOLOGY_SL_DRL_LED_RF,
            ];

            if (in_array($cell->getTechnology()->getId(), $specifics)) {
                $dependencies = [
                    [
                        'indicator' => Indicator::INDICATOR_PERCENTAGE_TIME_WITH_LIGHTS_ON,
                        'technology' => $cell->getTechnology()->getId(),
                    ],
                ];

                parent::resolveDependencies($dependencies, $cell, $em, false, $isUpload);
            }

            /** @var Cell $timeLightsOn */
            $timeLightsOn = $cellRepository->findOneByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                Indicator::INDICATOR_PERCENTAGE_TIME_WITH_LIGHTS_ON,
                $cell->getTechnology(),
                $cell->getYear()
            );

            if ($lifetimeHours->getValue() != 0 && $annualMileage->getValue() != 0 && $averageSpeed->getValue() != 0 && $timeLightsOn->getValue() != 0) {
                $value = $lifetimeHours->getValue() / ($annualMileage->getValue() / $averageSpeed->getValue() * $timeLightsOn->getValue());
            }
        }

        return $value;
    }
}
