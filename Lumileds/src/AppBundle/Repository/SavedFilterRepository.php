<?php

namespace AppBundle\Repository;

use AppBundle\Entity\SavedFilter;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class SavedFilterRepository extends EntityRepository
{
    /**
     * @param User $user
     *
     * @return SavedFilter
     */
    public function findOrCreateByUser(User $user): SavedFilter
    {
        $savedFilter = $this->findOneBy(['user' => $user]);

        if (!$savedFilter) {
            $savedFilter = new SavedFilter();
            $savedFilter->setUser($user);

            $this->getEntityManager()->persist($savedFilter);
        }

        return $savedFilter;
    }
}
