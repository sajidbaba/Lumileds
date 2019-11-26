<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="contribution_indicator_requests")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContributionIndicatorRequestRepository")
 *
 * @JMS\ExclusionPolicy("all")
 */
class ContributionIndicatorRequest
{
    const INDICATOR_GROUP_PARK_SPLIT_BY_TECHNOLOGY = 0;
    const INDICATOR_GROUP_UPGRADE_TAKE_RATE = 1;
    const INDICATOR_GROUP_PRICE_DEVELOPMENT = 4;

    const INDICATOR_GROUPS = [
        self::INDICATOR_GROUP_PARK_SPLIT_BY_TECHNOLOGY,
        self::INDICATOR_GROUP_UPGRADE_TAKE_RATE,
        self::INDICATOR_GROUP_PRICE_DEVELOPMENT,
    ];

    const STATUS_REQUIRED = 0;
    const STATUS_REVIEWED = 1;
    const STATUS_SUBMITTED = 2;
    const STATUS_APPROVED = 3;

    const STATUSES = [
        self::STATUS_REQUIRED,
        self::STATUS_REVIEWED,
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
     * @var int
     *
     * @ORM\Column(name="indicator_group", type="smallint")
     *
     * @JMS\Expose()
     * @JMS\Type("int")
     */
    private $indicatorGroup;

    /**
     * @var ContributionCountryRequest
     *
     * @ORM\ManyToOne(targetEntity="ContributionCountryRequest", inversedBy="contributionIndicatorRequests")
     * @ORM\JoinColumn(name="contribution_country_request_id", referencedColumnName="id", nullable=false)
     */
    private $contributionCountryRequest;

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
     * @return bool
     */
    public function isReviewed(): bool
    {
        return $this->getStatus() === self::STATUS_REVIEWED;
    }

    /**
     * @return int
     */
    public function getIndicatorGroup(): ?int
    {
        return $this->indicatorGroup;
    }

    /**
     * @param int $indicatorGroup
     *
     * @return self
     */
    public function setIndicatorGroup(int $indicatorGroup): self
    {
        if (!in_array($indicatorGroup, self::INDICATOR_GROUPS, true)) {
            throw new \InvalidArgumentException(sprintf('Unknown indicator group "%s"', $indicatorGroup));
        }

        $this->indicatorGroup = $indicatorGroup;

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
}
