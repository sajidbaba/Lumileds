<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Segment;

/**
 * @method Segment|null findOneByName($name)
 */
class SegmentRepository extends EntityRepository
{
    /**
     * Find all segment names
     *
     * @return array
     */
    public function findAllNames(): array
    {
        $names = [];

        /** @var Segment $entity */
        foreach ($this->findAll() as $entity) {
            $names[] = $entity->getName();
        }

        return $names;
    }
}
