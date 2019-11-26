<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="contribution_approves")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContributionApproveRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ContributionApprove
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     *
     * @JMS\Expose()
     */
    private $user;

    /**
     * @var ContributionCountryRequest
     *
     * @ORM\ManyToOne(targetEntity="ContributionCountryRequest", inversedBy="contributionApproves")
     * @ORM\JoinColumn(name="contribution_country_request_id", referencedColumnName="id", nullable=false)
     */
    private $contributionCountryRequest;

    /**
     * @var Segment
     *
     * @ORM\ManyToOne(targetEntity="Segment")
     * @ORM\JoinColumn(name="segment_id", referencedColumnName="id", nullable=false)
     */
    private $segment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     */
    private $createdAt;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return self
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return ContributionCountryRequest
     */
    public function getContributionCountryRequest(): ?ContributionCountryRequest
    {
        return $this->contributionCountryRequest;
    }

    /**
     * @param ContributionCountryRequest $contributionCountryRequest
     *
     * @return self
     */
    public function setContributionCountryRequest(ContributionCountryRequest $contributionCountryRequest): self
    {
        $this->contributionCountryRequest = $contributionCountryRequest;

        return $this;
    }

    /**
     * @return Segment
     */
    public function getSegment(): ?Segment
    {
        return $this->segment;
    }

    /**
     * @param Segment $segment
     *
     * @return self
     */
    public function setSegment(Segment $segment): self
    {
        $this->segment = $segment;

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
     * @ORM\PrePersist
     *
     * @return void
     */
    public function prePersist(): void
    {
        $this->createdAt = new \DateTime();
    }
}
