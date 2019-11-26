<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class SheetQueueRepository extends EntityRepository
{
    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     *
     * @return bool
     */
    public function isInitialUploadInProgress()
    {
        $numberOfUploades = $this->createQueryBuilder('sq')
            ->select('COUNT(DISTINCT sq.hash)')
            ->getQuery()
            ->getSingleScalarResult();

        if ($numberOfUploades == 1) {
            // check if this upload is in progress

            $numberOfUploaderInProgress = $this->createQueryBuilder('sq')
                ->select('COUNT(sq)')
                ->where('sq.processed = false')
                ->getQuery()
                ->getSingleScalarResult();

            if ($numberOfUploaderInProgress > 0) {
                return true;
            }
        }

        return false;
    }
}
