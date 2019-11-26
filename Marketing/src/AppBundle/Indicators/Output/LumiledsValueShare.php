<?php

namespace AppBundle\Indicators\Output;

use AppBundle\Entity\Cell;
use AppBundle\Entity\Indicator;
use AppBundle\Indicators\PercentageIndicatorInterface;
use AppBundle\Repository\CellRepository;
use Doctrine\Common\Persistence\ObjectManager;

class LumiledsValueShare extends AbstractOutputIndicator implements PercentageIndicatorInterface
{
    const id = Indicator::INDICATOR_LL_VALUE_SHARE;

    /**
     * LumiledsValueShare constructor.
     */
    public function __construct()
    {
        $this->name = 'Lumileds Value Share';
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

        /** @var Cell $marketValueUsd */
        $marketValueUsd = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_MARKET_VALUE_USD,
            $cell->getTechnology(),
            $cell->getYear()
        );

        /** @var Cell $llSalesUsd */
        $llSalesUsd = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_LL_SALES_USD,
            $cell->getTechnology(),
            $cell->getYear()
        );

        $value = null;
        if ($marketValueUsd && $llSalesUsd && $marketValueUsd->getValue() != 0) {
            $value = $llSalesUsd->getValue() / $marketValueUsd->getValue();
        }

        return $value;
    }
}
