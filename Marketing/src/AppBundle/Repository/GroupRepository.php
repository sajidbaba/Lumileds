<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * GroupRepository
 **/
class GroupRepository extends EntityRepository
{
    /**
     * Find group that has contributor role
     */
    public function findContributorGroup()
    {
        $qb = $this->createQueryBuilder('g');
        $qb
            ->where($qb->expr()->like('g.roles', ':role'))
            ->setParameter('role', '%"ROLE_CONTRIBUTOR"%');

        return $qb->getQuery()->getOneOrNullResult();
    }
}
