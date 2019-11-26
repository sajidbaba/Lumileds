<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class VersionRepository extends EntityRepository
{
    /**
     * Get last version id
     */
    public function getLastVersionId()
    {
        return $this->createQueryBuilder('v')
            ->select('MAX(v.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param bool $onlyCycle
     *
     * @return array
     */
    public function getOrdered($onlyCycle = true): array
    {
        $query = $this->createQueryBuilder('v');

        if ($onlyCycle) {
            $query->where('v.cycle = true');
        }

        return $query->orderBy('v.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
