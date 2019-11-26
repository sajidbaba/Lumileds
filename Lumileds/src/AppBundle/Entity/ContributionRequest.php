<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="contribution_requests")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContributionRequestRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @JMS\ExclusionPolicy("all")
 */
class ContributionRequest
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
     * @var \DateTime
     *
     * @ORM\Column(name="deadline", type="date")
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     */
    private $deadline;

    /**
     * @var Region
     *
     * @ORM\OneToOne(targetEntity="Region", inversedBy="contributionRequest")
     * @ORM\JoinColumn(name="region_id", referencedColumnName="id", nullable=false)
     */
    private $region;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var ContributionCountryRequest[]|null
     *
     * @ORM\OneToMany(targetEntity="ContributionCountryRequest", mappedBy="contributionRequest")
     */
    private $contributionCountryRequests;

    public function __construct()
    {
        $this->contributionCountryRequests = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getDeadline(): ?\DateTime
    {
        return $this->deadline;
    }

    /**
     * @param \DateTime $deadline
     *
     * @return self
     */
    public function setDeadline(\DateTime $deadline): self
    {
        $this->deadline = $deadline;

        return $this;
    }

    /**
     * @return Region
     */
    public function getRegion(): ?Region
    {
        return $this->region;
    }

    /**
     * @param Region $region
     *
     * @return self
     */
    public function setRegion(Region $region): self
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return self
     */
    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return ContributionCountryRequest[]|ArrayCollection
     */
    public function getContributionCountryRequests()
    {
        return $this->contributionCountryRequests;
    }

    /**
     * @param ContributionCountryRequest $contributionCountryRequest
     *
     * @return self
     */
    public function addContributionCountryRequest(ContributionCountryRequest $contributionCountryRequest): self
    {
        if (!$this->contributionCountryRequests->contains($contributionCountryRequest)) {
            $this->contributionCountryRequests->add($contributionCountryRequest);
        }

        return $this;
    }

    /**
     * @param ContributionCountryRequest $contributionCountryRequest
     *
     * @return self
     */
    public function removeContributionCountryRequest(ContributionCountryRequest $contributionCountryRequest): self
    {
        $this->contributionCountryRequests->removeElement($contributionCountryRequest);

        return $this;
    }

    /**
     * @ORM\PrePersist
     *
     * @return void
     */
    public function prePersist(): void
    {
        $this->createdAt = new \DateTime();
    }
}
