<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ContributionCountryRequest;
use Doctrine\ORM\EntityRepository;

class ContributionCountryRequestRepository extends EntityRepository
{
    /**
     * @param array $criteria
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return ContributionCountryRequest
     */
    public function findWithJoins(array $criteria): ContributionCountryRequest
    {
        return $this->createQueryBuilder('ccr')
            ->select('ccr, c, ca, co, ccm')
            ->innerJoin('ccr.country', 'c')
            ->leftJoin('ccr.contributionApproves', 'ca')
            ->leftJoin('ccr.contributions', 'co')
            ->leftJoin('co.contributionCellModifications', 'ccm')
            ->leftJoin('ccr.contributionIndicatorRequests', 'cir')
            ->where('c.id = :countryId')
            ->andWhere('c.active = 1')
            ->setParameter('countryId', $criteria['country'])
            ->getQuery()
            ->getSingleResult();
    }
}
