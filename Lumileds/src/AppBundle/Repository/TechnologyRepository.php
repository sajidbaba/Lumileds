<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Technology;

/**
 * @method Technology|null findOneByName($name)
 */
class TechnologyRepository extends EntityRepository
{
}
