<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @ORM\Table(name="users")
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 *
 * @JMS\ExclusionPolicy("all")
 */
class User extends BaseUser
{
    const ROLE_CONTRIBUTOR = 'ROLE_CONTRIBUTOR';
    const ROLE_ADMIN = 'ROLE_ADMIN';

    const ROLES = [
        self::ROLE_DEFAULT,
        self::ROLE_CONTRIBUTOR,
        self::ROLE_ADMIN,
    ];

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Group
     *
     * @ORM\ManyToOne(targetEntity="Group")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    protected $group;

    /**
     * @var Country[]
     *
     * @ORM\ManyToMany(targetEntity="Country", inversedBy="users")
     */
    protected $countries;

    /**
     * @var SheetQueue[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="SheetQueue", mappedBy="user")
     */
    protected $queues;

    /**
     * @var Contribution[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Contribution", mappedBy="user", cascade={"remove"})
     */
    private $contributions;

    /**
     * @var ContributionCellModification[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ContributionCellModification", mappedBy="user")
     */
    private $contributionCellModifications;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->countries = new ArrayCollection();
        $this->queues = new ArrayCollection();
        $this->contributions = new ArrayCollection();
        $this->contributionCellModifications = new ArrayCollection();
    }

    /**
     * @param Group $group
     *
     * @return User
     */
    public function setGroup(Group $group): User
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return Group
     */
    public function getGroup(): ?Group
    {
        return $this->group;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroups()
    {
        return $this->group ? new ArrayCollection([$this->group]) : new ArrayCollection();
    }

    /**
     * Get country
     *
     * @return Country[]
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * Add country
     *
     * @param Country $country
     * @return User
     */
    public function addCountry(Country $country): User
    {
        if ($this->isContributor() && !$this->countries->contains($country)) {
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
     * Check if user is a contributor
     */
    public function isContributor(): bool
    {
        return $this->hasRole('ROLE_CONTRIBUTOR');
    }

    /**
     * Check if user is a admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('ROLE_ADMIN');
    }

    /**
     * Add queue
     *
     * @param SheetQueue $queue
     *
     * @return User
     */
    public function addQueue(SheetQueue $queue)
    {
        if (!$this->queues->contains($queue)) {
            $this->queues->add($queue);
        }

        return $this;
    }

    /**
     * Remove queue
     *
     * @param SheetQueue $queue
     *
     * @return User
     */
    public function removeQueue(SheetQueue $queue)
    {
        $this->queues->removeElement($queue);

        return $this;
    }

    /**
     * Get queues
     *
     * @return SheetQueue[]|ArrayCollection
     */
    public function getQueues()
    {
        return $this->queues;
    }

    /**
     * Get name used for create by fields
     */
    public function getBlame(): string
    {
        return $this->getUsername();
    }

    /**
     * @return Contribution[]|ArrayCollection
     */
    public function getContributions()
    {
        return $this->contributions;
    }

    /**
     * @param Contribution $contribution
     *
     * @return self
     */
    public function addContribution(Contribution $contribution): self
    {
        if (!$this->contributions->contains($contribution)) {
            $this->contributions->add($contribution);
        }

        return $this;
    }

    /**
     * @param Contribution $contribution
     *
     * @return self
     */
    public function removeContribution(Contribution $contribution): self
    {
        $this->contributions->removeElement($contribution);

        return $this;
    }

    /**
     * @return ContributionCellModification[]|ArrayCollection
     */
    public function getContributionCellModifications()
    {
        return $this->contributionCellModifications;
    }

    /**
     * @param ContributionCellModification $ContributionCellModification
     *
     * @return self
     */
    public function addContributionCellModification(ContributionCellModification $ContributionCellModification): self
    {
        if (!$this->contributionCellModifications->contains($ContributionCellModification)) {
            $this->contributionCellModifications->add($ContributionCellModification);
        }

        return $this;
    }

    /**
     * @param ContributionCellModification $ContributionCellModification
     *
     * @return self
     */
    public function removeContributionCellModification(ContributionCellModification $ContributionCellModification): self
    {
        $this->contributionCellModifications->removeElement($ContributionCellModification);

        return $this;
    }
}
