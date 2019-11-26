<?php

namespace AppBundle\Services;

use AppBundle\Entity\Country;
use AppBundle\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;

class CountryService
{
    /**
     * @var CountryRepository
     */
    private $repo;

    /**
     * TableService constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(Country::class);
    }

    /**
     * Get table
     *
     * @return array
     */
    public function getCountries(): array
    {
      return $this->repo->findBy(['active' => true]);
    }
}
