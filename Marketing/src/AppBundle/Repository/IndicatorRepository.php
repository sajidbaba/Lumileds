<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Indicator;
use AppBundle\Entity\Technology;
use Doctrine\ORM\EntityRepository;

class IndicatorRepository extends EntityRepository
{
    /**
     * Fetches the indicator entity attached to a certain technology.
     *
     * @param $name
     *   Indicator name.
     * @param Technology $technology
     *   technology entity.
     *
     * @return Indicator|null
     *   Indicator entity object, null if not found.
     */
    public function findByNameAndTechnology($name, Technology $technology = null): ?Indicator
    {
        $indicatorEntity = null;

        if ($technology) {
            $indicatorEntity = $this->findOneBy([
                'name' => $name,
                'technology' => $technology,
            ]);
        }

        if (!$indicatorEntity) {
            $indicatorEntity = $this->findOneBy([
                'name' => $name,
                'technology' => null,
            ]);
        }

        return $indicatorEntity;
    }
}
