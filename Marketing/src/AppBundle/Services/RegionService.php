<?php

namespace AppBundle\Services;

use AppBundle\Entity\Country;
use AppBundle\Entity\Region;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class RegionService
{
    /** @var EntityManagerInterface */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return Region[]
     */
    public function getRegions()
    {
        return $this->em->getRepository(Region::class)->findAll();
    }

    /**
     * If user is admin, all regions with all countries will be returned.
     * If user is contributor, only related regions with related countries will be returned.
     *
     * @param User $user
     *
     * @return Region[]
     */
    public function getRegionsRelatedToUser(User $user)
    {
        $repository = $this->em->getRepository(Region::class);

        if ($user->hasRole('ROLE_ADMIN')) {
            return $repository->findAll();
        }

        return $repository->findByCountries($user->getCountries());
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param ArrayCollection $originalCountries
     *
     * @return bool
     */
    public function handle(FormInterface $form, Request $request, ArrayCollection $originalCountries)
    {
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return false;
        }

        $validRegion = $form->getData();

        $this->saveRegion($validRegion, $originalCountries);

        return true;

    }

    /**
     * @param Region $region
     * @param Country[]|ArrayCollection $originalCountries
     */
    public function saveRegion(Region $region, ArrayCollection $originalCountries)
    {
        foreach ($originalCountries as $originalCountry) {
            if (!$region->getCountries()->contains($originalCountry)) {
                $originalCountry->setRegion(null);
            }
        }

        foreach ($region->getCountries() as $country) {
            $country->setRegion($region);
        }

        $this->em->persist($region);
        $this->em->flush();
    }
}
