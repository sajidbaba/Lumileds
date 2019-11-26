<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Country;

/**
 * @method Country|null findOneByName($name)
 */
class CountryRepository extends EntityRepository
{
    /**
     * Find all country names
     *
     * @return array
     */
    public function findAllNames(): array
    {
        $names = [];

        /** @var Country $entity */
        foreach ($this->findAll() as $entity) {
            $names[] = $entity->getName();
        }

        return $names;
    }

    /**
     * @return Country[]
     */
    public function getOrderedByName(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.name')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $name
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return Country|null
     */
    public function findOneActiveByName($name): ?Country
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.name = :name')
            ->andWhere('c.active = 1')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
