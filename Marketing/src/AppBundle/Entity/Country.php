<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Country
 *
 * @ORM\Table(name="countries")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CountryRepository")
 *
 * @JMS\ExclusionPolicy("all")
 * @JMS\VirtualProperty(
 *     "regionId",
 *     exp="object.getRegion().getId()",
 *     options={@JMS\SerializedName("region_id")}
 *  )
 */
class Country
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
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     */
    private $active = true;

    /**
     * @var Region
     *
     * @ORM\ManyToOne(targetEntity="Region", inversedBy="countries")
     * @ORM\JoinColumn(name="region_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $region;

    /**
     * @var Indicator[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Indicator", mappedBy="country")
     */
    private $indicators;

    /**
     * @var Cell[]
     *
     * @ORM\OneToMany(targetEntity="Cell", mappedBy="country")
     */
    private $cells;

    /**
     * @var ContributionCountryRequest|null
     *
     * @ORM\OneToOne(targetEntity="ContributionCountryRequest", mappedBy="country")
     *
     * @JMS\Expose()
     */
    private $contributionCountryRequest;

    /**
     * @var User[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="User", mappedBy="countries")
     */
    private $users;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->indicators = new ArrayCollection();
        $this->cells = new ArrayCollection();
        $this->users = new ArrayCollection();
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
     * @return Country
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
     * Set active
     *
     * @param bool $active
     *
     * @return Country
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Is active
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Set region
     *
     * @param Region $region
     *
     * @return Country
     */
    public function setRegion(Region $region = null)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return Region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Add indicator
     *
     * @param Indicator $indicator
     *
     * @return Country
     */
    public function addIndicator(Indicator $indicator)
    {
        if (!$this->indicators->contains($indicator)) {
            $this->indicators->add($indicator);
        }

        return $this;
    }

    /**
     * Remove indicator
     *
     * @param Indicator $indicator
     */
    public function removeIndicator(Indicator $indicator)
    {
        $this->indicators->removeElement($indicator);
    }

    /**
     * Get indicators
     *
     * @return Indicator[]|ArrayCollection
     */
    public function getIndicators()
    {
        return $this->indicators;
    }

    /**
     * Add cell
     *
     * @param Cell $cell
     *
     * @return Country
     */
    public function addCell(Cell $cell)
    {
        if (!$this->cells->contains($cell)) {
            $this->cells->add($cell);
        }

        return $this;
    }

    /**
     * Remove cell
     *
     * @param Cell $cell
     */
    public function removeCell(Cell $cell)
    {
        $this->cells->removeElement($cell);
    }

    /**
     * Get cells
     *
     * @return Cell[]|ArrayCollection
     */
    public function getCells()
    {
        return $this->cells;
    }

    /**
     * @return ContributionCountryRequest|null
     */
    public function getContributionCountryRequest(): ?ContributionCountryRequest
    {
        return $this->contributionCountryRequest;
    }

    /**
     * @param ContributionCountryRequest|null $contributionCountryRequest
     *
     * @return self
     */
    public function setContributionCountryRequest(?ContributionCountryRequest $contributionCountryRequest): self
    {
        $this->contributionCountryRequest = $contributionCountryRequest;

        return $this;
    }

    /**
     * @return User[]|ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }


    /**
     * @param User $user
     *
     * @return self
     */
    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    /**
     * @param User $user
     *
     * @return self
     */
    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->id;
    }
}
