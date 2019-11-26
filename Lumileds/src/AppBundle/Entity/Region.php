<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Region
 *
 * @ORM\Table(name="regions")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RegionRepository")
 *
 * @UniqueEntity("name")
 *
 * @JMS\ExclusionPolicy("all")
 */
class Region
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @JMS\Expose()
     * @JMS\Type("int")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    private $name;

    /**
     * @var Country[]
     *
     * @ORM\OneToMany(targetEntity="Country", mappedBy="region", cascade={"persist"})
     *
     * @JMS\Expose()
     */
    private $countries;

    /**
     * @var ContributionRequest|null
     *
     * @ORM\OneToOne(targetEntity="ContributionRequest", mappedBy="region")
     *
     * @JMS\Expose()
     */
    private $contributionRequest;

    public function __construct()
    {
        $this->countries = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Region
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add country
     *
     * @param Country $country
     *
     * @return Region
     */
    public function addCountry(Country $country)
    {
        if (!$this->countries->contains($country)) {
            $this->countries->add($country);
        }

        return $this;
    }

    /**
     * Remove country
     *
     * @param Country $country
     */
    public function removeCountry(Country $country)
    {
        $this->countries->removeElement($country);
    }

    /**
     * Get countries
     *
     * @return Country[]|ArrayCollection
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * @return ContributionRequest
     */
    public function getContributionRequest(): ?ContributionRequest
    {
        return $this->contributionRequest;
    }

    /**
     * @param ContributionRequest $contributionRequest
     *
     * @return self
     */
    public function setContributionRequest(ContributionRequest $contributionRequest): self
    {
        $this->contributionRequest = $contributionRequest;

        return $this;
    }
}
