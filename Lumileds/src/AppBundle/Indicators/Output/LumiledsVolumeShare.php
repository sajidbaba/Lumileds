<?php

namespace AppBundle\Indicators\Output;

use AppBundle\Entity\Cell;
use AppBundle\Entity\Indicator;
use AppBundle\Indicators\PercentageIndicatorInterface;
use AppBundle\Repository\CellRepository;
use Doctrine\Common\Persistence\ObjectManager;

class LumiledsVolumeShare extends AbstractOutputIndicator implements PercentageIndicatorInterface
{
    const id = Indicator::INDICATOR_LL_VOLUME_SHARE;

    /**
     * LumiledsVolumeShare constructor.
     */
    public function __construct()
    {
        $this->name = 'Lumileds Volume Share';
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

        $cellTechnology = $cell->getTechnology()->getId();

        $dependencies = [
            [
                'indicator' => Indicator::INDICATOR_MARKET_VOLUME,
                'technology' => $cellTechnology,
            ]
        ];
        parent::resolveDependencies($dependencies, $cell, $em, false, $isUpload);

        /** @var Cell $llVolume */
        $llVolume = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_LL_VOLUME,
            $cellTechnology,
            $cell->getYear()
        );

        /** @var Cell $marketVolume */
        $marketVolume = $cellRepository->findOneByIndicatorTechnologyAndYear(
            $cell->getCountry(),
            $cell->getSegment(),
            Indicator::INDICATOR_MARKET_VOLUME,
            $cellTechnology,
            $cell->getYear()
        );

        $value = null;
        if ($llVolume && $marketVolume && $marketVolume->getValue() != 0) {
            $value = $llVolume->getValue() / $marketVolume->getValue();
        }

        return $value;
    }
}
