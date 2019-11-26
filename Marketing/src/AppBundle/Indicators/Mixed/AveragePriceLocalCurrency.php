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

/**
 * Class AveragePriceLocalCurrency
 *
 * Cell->Year <= now
 *   [Cell->Value]
 * Cell->Year > now
 *   [ASP LC->(Year - 1) * (1 - PriceErosion)]
 */
class AveragePriceLocalCurrency extends AbstractIndicator implements InputIndicatorInterface, OutputIndicatorInterface
{
    use Validation\IsDecimalGreaterThanZero;
    use Validation\IsGreaterOrLessOnFivePercent;

    const id = Indicator::INDICATOR_ASP_LC;

    /**
     * AveragePriceLocalCurrency constructor.
     */
    public function __construct()
    {
        $this->name = 'ASP LC';
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
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function isEditable(Cell $cell): bool
    {
        $isHiPer = $cell->getTechnology()->getId() == Technology::TECHNOLOGY_SL_HIPER;

        return $isHiPer || $cell->getYear() < date('Y');
    }

    /**
     * {@inheritdoc}
     */
    public function getCalculatedValue(Cell $cell, ObjectManager $em, $isUpload)
    {
        $isHiPer = $cell->getTechnology()->getId() == Technology::TECHNOLOGY_SL_HIPER;

        if ($isHiPer || $cell->getYear() < date('Y')) {
            return $cell->getValue();
        } else {
            /** @var CellRepository $cellRepository */
            $cellRepository = $em->getRepository(Cell::class);

            $dependencies = [
                [
                    'indicator' => Indicator::INDICATOR_ASP_LC,
                    'technology' => $cell->getTechnology()->getId(),
                    'year' => $cell->getYear() - 1
                ]
            ];

            parent::resolveDependencies($dependencies, $cell, $em, true, $isUpload);

            $aspLc = $cellRepository->findOneByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                Indicator::INDICATOR_ASP_LC,
                $cell->getTechnology(),
                $cell->getYear() - 1
            );

            $priceDevelopment = $cellRepository->findOneByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                Indicator::INDICATOR_PRICE_DEVELOPMENT,
                $cell->getTechnology(),
                $cell->getYear()
            );

            if ($aspLc && $priceDevelopment) {
                return $aspLc->getValue() * (1 + (float)$priceDevelopment->getValue());
            }
        }

        return null;
    }
}
