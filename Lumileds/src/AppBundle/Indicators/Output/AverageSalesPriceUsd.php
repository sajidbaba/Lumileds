<?php

namespace AppBundle\Indicators\Output;

use AppBundle\Entity\Cell;
use AppBundle\Entity\Indicator;
use AppBundle\Repository\CellRepository;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class AverageSalesPriceUsd
 *
 * [ASP LC * ExchangeRate]
 */
class AverageSalesPriceUsd extends AbstractOutputIndicator
{
    const id = Indicator::INDICATOR_ASP_USD;

    /**
     * AverageSalesPriceUsd constructor.
     */
    public function __construct()
    {
        $this->name = 'ASP USD';
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
    public function getCalculatedValue(Cell $cell, ObjectManager $em, $isUpload)
    {
        $dependencies = [];
        if ($cell->getYear() == date('Y')) {
            $dependencies[] = [
                'indicator' => Indicator::INDICATOR_ASP_LC,
                'technology' => null,
            ];

            parent::resolveDependencies($dependencies, $cell, $em, false, $isUpload);
        }

        /** @var CellRepository $cellRepository */
        $cellRepository = $em->getRepository(Cell::class);

        /** @var Cell $aspLc */
        $aspLc = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_ASP_LC,
            $cell->getTechnology(),
            $cell->getYear()
        );

        /** @var Cell $exchangeRate */
        $exchangeRate = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_EXCHANGE_RATE,
            null,
            $cell->getYear()
        );

        $value = null;
        if ($aspLc && $exchangeRate) {
            $value = $aspLc->getValue() * $exchangeRate->getValue();
        }

        return $value;
    }
}
