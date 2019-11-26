<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="contribution_country_requests")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContributionCountryRequestRepository")
 *
 * @JMS\ExclusionPolicy("all")
 */
class ContributionCountryRequest
{
    const STATUS_REQUIRED = 0;
    const STATUS_REMINDED = 1;
    const STATUS_SUBMITTED = 2;
    const STATUS_APPROVED = 3;

    const STATUSES = [
        self::STATUS_REQUIRED,
        self::STATUS_REMINDED,
        self::STATUS_SUBMITTED,
        self::STATUS_APPROVED,
    ];

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
     * @var int
     *
     * @ORM\Column(name="status", type="smallint")
     *
     * @JMS\Expose()
     * @JMS\Type("int")
     */
    private $status = self::STATUS_REQUIRED;

    /**
     * @var Country
     *
     * @ORM\OneToOne(targetEntity="Country", inversedBy="contributionCountryRequest")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=false)
     *
     * @JMS\Expose()
     */
    private $country;

    /**
     * @var ContributionRequest
     *
     * @ORM\ManyToOne(targetEntity="ContributionRequest", inversedBy="contributionCountryRequests")
     * @ORM\JoinColumn(name="contribution_request_id", referencedColumnName="id", nullable=false)
     */
    private $contributionRequest;

    /**
     * @var Contribution[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Contribution", mappedBy="contributionCountryRequest")
     */
    private $contributions;

    /**
     * @var ContributionApprove[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ContributionApprove", mappedBy="contributionCountryRequest")
     */
    private $contributionApproves;

    /**
     * @var ContributionApproveRow[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ContributionApproveRow", mappedBy="contributionCountryRequest")
     */
    private $contributionApproveRows;

    /**
     * @var ContributionIndicatorRequest[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ContributionIndicatorRequest", mappedBy="contributionCountryRequest")
     *
     * @JMS\Expose()
     */
    private $contributionIndicatorRequests;

    public function __construct()
    {
        $this->contributions = new ArrayCollection();
        $this->contributionApproves = new ArrayCollection();
        $this->contributionApproveRows = new ArrayCollection();
        $this->contributionIndicatorRequests = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * @param int $status
     *
     * @return self
     */
    public function setStatus(int $status): self
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException(sprintf('Unknown status "%s"', $status));
        }

        $this->status = $status;

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
     * @return ContributionRequest
     */
    public function getContributionRequest() : ?ContributionRequest
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
     * @return ContributionApprove[]|ArrayCollection
     */
    public function getContributionApproves()
    {
        return $this->contributionApproves;
    }

    /**
     * @param ContributionApprove $contributionApprove
     *
     * @return self
     */
    public function addContributionApprove(ContributionApprove $contributionApprove): self
    {
        if (!$this->contributionApproves->contains($contributionApprove)) {
            $this->contributionApproves->add($contributionApprove);
        }

        return $this;
    }

    /**
     * @param ContributionApprove $contributionApprove
     *
     * @return self
     */
    public function removeContributionApprove(ContributionApprove $contributionApprove): self
    {
        $this->contributionApproves->removeElement($contributionApprove);

        return $this;
    }

    /**
     * @JMS\VirtualProperty()
     *
     * @return Contribution|null
     */
    public function getLastContribution(): ?Contribution
    {
        return $this->contributions->count() ? $this->contributions->last() : null;
    }

    /**
     * @return ContributionApproveRow[]|ArrayCollection
     */
    public function getContributionApproveRows()
    {
        return $this->contributionApproveRows;
    }

    /**
     * @param ContributionApproveRow $contributionApproveRow
     *
     * @return self
     */
    public function addContributionApproveRow(ContributionApproveRow $contributionApproveRow): self
    {
        if (!$this->contributionApproveRows->contains($contributionApproveRow)) {
            $this->contributionApproveRows->add($contributionApproveRow);
        }

        return $this;
    }

    /**
     * @param ContributionApproveRow $contributionApproveRow
     *
     * @return self
     */
    public function removeContributionApproveRow(ContributionApproveRow $contributionApproveRow): self
    {
        $this->contributionApproveRows->removeElement($contributionApproveRow);

        return $this;
    }

    /**
     * @return ContributionIndicatorRequest[]|ArrayCollection
     */
    public function getContributionIndicatorRequests()
    {
        return $this->contributionIndicatorRequests;
    }

    /**
     * @param ContributionIndicatorRequest $contributionIndicatorRequest
     *
     * @return self
     */
    public function addContributionIndicatorRequest(ContributionIndicatorRequest $contributionIndicatorRequest): self
    {
        if (!$this->contributionIndicatorRequests->contains($contributionIndicatorRequest)) {
            $this->contributionIndicatorRequests->add($contributionIndicatorRequest);
        }

        return $this;
    }

    /**
     * @param ContributionIndicatorRequest $contributionIndicatorRequest
     *
     * @return self
     */
    public function removeContributionIndicatorRequest(ContributionIndicatorRequest $contributionIndicatorRequest): self
    {
        $this->contributionIndicatorRequests->removeElement($contributionIndicatorRequest);

        return $this;
    }
}
