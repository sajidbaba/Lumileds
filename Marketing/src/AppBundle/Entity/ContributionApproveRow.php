<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="contribution_approve_rows")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContributionApproveRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ContributionApproveRow
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
     * @ORM\ManyToOne(targetEntity="ContributionCountryRequest", inversedBy="contributionApproveRows")
     * @ORM\JoinColumn(name="contribution_country_request_id", referencedColumnName="id", nullable=false)
     */
    private $contributionCountryRequest;

    /**
     * @var Indicator
     *
     * @ORM\ManyToOne(targetEntity="Indicator")
     * @ORM\JoinColumn(name="indicator_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $indicator;

    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=false)
     */
    private $country;

    /**
     * @var Segment
     *
     * @ORM\ManyToOne(targetEntity="Segment")
     * @ORM\JoinColumn(name="segment_id", referencedColumnName="id", nullable=false)
     */
    private $segment;

    /**
     * @var Technology|null
     *
     * @ORM\ManyToOne(targetEntity="Technology")
     * @ORM\JoinColumn(name="technology_id", referencedColumnName="id", nullable=true)
     */
    private $technology;

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
     * @return Indicator
     */
    public function getIndicator(): ?Indicator
    {
        return $this->indicator;
    }

    /**
     * @param Indicator $indicator
     *
     * @return self
     */
    public function setIndicator(Indicator $indicator): self
    {
        $this->indicator = $indicator;

        return $this;
    }

    /**
     * @return Country
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @param Country $country
     *
     * @return self
     */
    public function setCountry(Country $country): self
    {
        $this->country = $country;

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
     * @return Technology|null
     */
    public function getTechnology(): ?Technology
    {
        return $this->technology;
    }

    /**
     * @param Technology|null $technology
     *
     * @return self
     */
    public function setTechnology(?Technology $technology): self
    {
        $this->technology = $technology;

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
