<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\SheetQueue;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SheetQueueListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * SheetQueueListener constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Invokes logic when a new SheetQueue entity is about
     * to be persisted.
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof SheetQueue) {
            return;
        }

        $this->setHash($entity);
        $this->setUser($entity);
    }

    /**
     * Sets unique hash for the sheet chunks.
     *
     * @param SheetQueue $entity
     */
    private function setHash(SheetQueue $entity)
    {
        static $hash = null;
        if (!$hash) {
            $hash = sha1(time());
        }

        $entity->setHash($hash);
    }

    /**
     * Sets the owner of the uploaded sheet chunks.
     *
     * @param SheetQueue $entity
     */
    private function setUser(SheetQueue $entity)
    {
        $userEntity = $this->container->get('security.token_storage')
            ->getToken()->getUser();

        $entity->setUser($userEntity);
    }
}
