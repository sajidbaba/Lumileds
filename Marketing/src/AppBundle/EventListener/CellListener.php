<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Cell;
use AppBundle\Indicators\IndicatorInterface;
use AppBundle\Indicators\Output\OutputIndicatorInterface;
use AppBundle\Services\IndicatorService;
use Doctrine\ORM\Event\LifecycleEventArgs;

class CellListener
{
    /**
     * @var IndicatorService
     */
    private $indicatorRegistry;

    /**
     * CellListener constructor.
     *
     * @param IndicatorService $indicatorRegistry
     *   Indicator registry service.
     */
    public function __construct(IndicatorService $indicatorRegistry)
    {
        $this->indicatorRegistry = $indicatorRegistry;
    }

    /**
     * Invokes logic when entity is loaded.
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // TODO: DRY.
        if (!$entity instanceof Cell) {
            return;
        }

        $indicatorEntity = $entity->getIndicator();
        /** @var IndicatorInterface|OutputIndicatorInterface $indicatorClass */
        $indicatorClass = $this->indicatorRegistry->getIndicatorById($indicatorEntity->getId());
        $entity->setIndicatorClass($indicatorClass);
    }
}
