<?php

namespace AppBundle\Repository;

use AppBundle\Entity\CellError;
use Doctrine\ORM\EntityRepository;

class CellErrorRepository extends EntityRepository
{
    /**
     * Remove all errors that are not related to cells
     */
    public function removeUploadErrors(): void
    {
        $this->createQueryBuilder('c')
            ->delete()
            ->where('c.type = :type')
            ->setParameter('type', CellError::TYPE_FILE_ERROR)
            ->getQuery()
            ->execute();
    }

    /**
     * Get all errors that are not related to cells
     *
     * @return CellError[]
     */
    public function findUploadErrorMessages()
    {
        return $this
            ->createQueryBuilder('c')
            ->select('c.message')
            ->where('c.cell IS NULL')
            ->groupBy('c.message')
            ->getQuery()
            ->getResult();
    }
}
