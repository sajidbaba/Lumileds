<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ContributionCountryRequest;
use AppBundle\Entity\Country;
use AppBundle\Entity\Region;
use Doctrine\ORM\EntityRepository;

class RegionRepository extends EntityRepository
{
    /**
     * Find Regions with specific countries.
     * Notice: regions will contains only countries received for filtering!
     *
     * @param Country[] $countries
     *
     * @return Region[]
     */
    public function findByCountries($countries)
    {
        return $this->createQueryBuilder('r')
            ->select('r, c')
            ->join('r.countries', 'c')
            ->where('c IN (:countries)')
            ->andWhere('c.active = 1')
            ->setParameter('countries', $countries)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Region[]
     */
    public function getBeforeDeadline()
    {
        $date = new \DateTime();
        $date->modify('+3 day');

        return $this->createQueryBuilder('r')
            ->select('r, c, cr')
            ->join('r.countries', 'c')
            ->join('c.contributionCountryRequest', 'ccr')
            ->join('r.contributionRequest', 'cr')
            ->where("DATE_FORMAT(cr.deadline, '%Y-%m-%d') = DATE_FORMAT(:date, '%Y-%m-%d')")
            ->andWhere('ccr.status != :status')
            ->setParameter('date', $date)
            ->setParameter('status', ContributionCountryRequest::STATUS_APPROVED)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Region[]
     */
    public function getOrderedByName()
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.name')
            ->getQuery()
            ->getResult();
    }
}
