<?php

namespace AppBundle\Indicators\Mixed;

use AppBundle\Entity\Cell;
use AppBundle\Entity\Indicator;
use AppBundle\Indicators\AbstractIndicator;
use AppBundle\Indicators\Input\InputIndicatorInterface;
use AppBundle\Indicators\Input\Validation\IsGreaterOrLessOnFivePercent;
use AppBundle\Indicators\Input\Validation\Percentage;
use AppBundle\Indicators\Output\OutputIndicatorInterface;
use AppBundle\Indicators\PercentageIndicatorInterface;
use AppBundle\Repository\CellRepository;
use Doctrine\Common\Persistence\ObjectManager;

class PriceDevelopment extends AbstractIndicator implements InputIndicatorInterface, OutputIndicatorInterface, PercentageIndicatorInterface
{
    use Percentage;
    use IsGreaterOrLessOnFivePercent;

    const id = Indicator::INDICATOR_PRICE_DEVELOPMENT;

    /**
     * PriceDevelopment constructor.
     */
    public function __construct()
    {
        $this->name = 'Price Development';
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
        return 1;
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
        return $cell->getYear() >= date('Y');
    }

    /**
     * {@inheritdoc}
     */
    public function getCalculatedValue(Cell $cell, ObjectManager $em, $isUpload)
    {
        if ($cell->getYear() >= date('Y')) {
            return $cell->getValue();
        } else {
            /** @var CellRepository $cellRepository */
            $cellRepository = $em->getRepository(Cell::class);

            /** @var Cell $yearOne */
            $yearOne = $cellRepository->findOneByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                Indicator::INDICATOR_ASP_LC,
                $cell->getTechnology(),
                $cell->getYear() - 1
            );

            /** @var Cell $yearTwo */
            $yearTwo = $cellRepository->findOneByIndicatorTechnologyAndYear(
                $cell->getCountry(),
                $cell->getSegment(),
                Indicator::INDICATOR_ASP_LC,
                $cell->getTechnology(),
                $cell->getYear()
            );

            if ($yearOne && $yearTwo && $yearOne->getValue() != 0) {
                return $yearTwo->getValue() / $yearOne->getValue() - 1;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(Cell $cell): bool
    {
        $value = $cell->getValue();

        if ($value === null) {
            return true;
        }

        return is_numeric($value) && $value <= 1;
    }
}
