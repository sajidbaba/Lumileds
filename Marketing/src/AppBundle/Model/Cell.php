<?php

namespace AppBundle\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Cell
 */
class Cell
{
    /**
     * @var int
     *
     * @JMS\Type("int")
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Exclude()
     */
    private $country;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $region;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $segment;

    /**
     * @var string
     *
     * @JMS\Exclude()
     */
    private $indicator;

    /**
     * @JMS\Exclude()
     *
     * @var int
     */
    private $indicatorId;

    /**
     * @var string
     *
     * @JMS\Exclude()
     */
    private $technology;

    /**
     * @JMS\Exclude()
     *
     * @var int
     */
    private $technologyId;

    /**
     * @var string
     *
     * @JMS\Exclude()
     */
    private $year;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $value;

    /**
     * @var string|null
     *
     * @JMS\Type("string")
     */
    private $error;

    /**
     * @var integer|null
     *
     * @JMS\Type("integer")
     */
    private $errorType;

    /**
     * @var integer
     *
     * @JMS\Type("integer")
     */
    private $precision;

    /**
     * @var bool
     *
     * @JMS\Type("bool")
     */
    private $isEditable;

    /**
     * @var bool
     *
     * @JMS\Type("bool")
     */
    private $isPercentage;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $versionValue;

    /**
     * If cell has contribution
     *
     * @var bool
     *
     * @JMS\Type("bool")
     */
    private $contributed = false;

    /**
     * If cell has contribution made by contributor
     *
     * @var bool
     *
     * @JMS\Type("bool")
     */
    private $contributedByContributor = false;

    /**
     * Set id
     *
     * @param int $id
     *
     * @return Cell
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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
     * Set year
     *
     * @param string $year
     *
     * @return Cell
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return string
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return Cell
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return Cell
     */
    public function setCountry(string $country = null): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * Set segment
     *
     * @param string $segment
     *
     * @return Cell
     */
    public function setSegment(string $segment = null): self
    {
        $this->segment = $segment;

        return $this;
    }

    /**
     * Get segment
     *
     * @return string
     */
    public function getSegment(): string
    {
        return $this->segment;
    }

    /**
     * Set indicator
     *
     * @param string $indicator
     *
     * @return Cell
     */
    public function setIndicator(string $indicator = null): self
    {
        $this->indicator = $indicator;

        return $this;
    }

    /**
     * Get indicator
     *
     * @return string
     */
    public function getIndicator(): string
    {
        return $this->indicator;
    }

    /**
     * Set technology
     *
     * @param string $technology
     *
     * @return Cell
     */
    public function setTechnology(string $technology = null): self
    {
        $this->technology = $technology;

        return $this;
    }

    /**
     * Get technology
     *
     * @return string
     */
    public function getTechnology(): ?string
    {
        return $this->technology;
    }

    /**
     * @param string|null $error
     *
     * @return Cell
     */
    public function setError(string $error = null): self
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @return string
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @param int|null $errorType
     *
     * @return Cell
     */
    public function setErrorType(?int $errorType): self
    {
        $this->errorType = $errorType;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getErrorType(): ?int
    {
        return $this->errorType;
    }

    /**
     * @param int $precision
     *
     * @return Cell
     */
    public function setPrecision(int $precision): self
    {
        $this->precision = $precision;

        return $this;
    }

    /**
     * @return int
     */
    public function getPrecision(): int
    {
        return $this->precision;
    }

    /**
     * @param bool $isEditable
     *
     * @return Cell
     */
    public function setIsEditable(bool $isEditable): self
    {
        $this->isEditable = $isEditable;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEditable(): bool
    {
        return $this->isEditable;
    }

    /**
     * @param bool $isPercentage
     *
     * @return Cell
     */
    public function setIsPercentage(bool $isPercentage): self
    {
        $this->isPercentage = $isPercentage;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPercentage(): bool
    {
        return $this->isPercentage;
    }

    /**
     * @param string $versionValue
     *
     * @return Cell
     */
    public function setVersionValue(string $versionValue): self
    {
        $this->versionValue = $versionValue;

        return $this;
    }

    /**
     * @return string
     */
    public function getVersionValue(): string
    {
        return $this->versionValue;
    }

    /**
     * @return int
     */
    public function getRegion(): ?int
    {
        return $this->region;
    }

    /**
     * @param int|null $region
     *
     * @return Cell
     */
    public function setRegion(int $region = null): self
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return int
     */
    public function getIndicatorId() : ?int
    {
        return $this->indicatorId;
    }

    /**
     * @param int $indicatorId
     *
     * @return self
     */
    public function setIndicatorId(?int $indicatorId) : self
    {
        $this->indicatorId = $indicatorId;

        return $this;
    }

    /**
     * @return int
     */
    public function getTechnologyId() : ?int
    {
        return $this->technologyId;
    }

    /**
     * @param int $technologyId
     *
     * @return self
     */
    public function setTechnologyId(?int $technologyId) : self
    {
        $this->technologyId = $technologyId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isContributed() : ?bool
    {
        return $this->contributed;
    }

    /**
     * @param bool $contributed
     *
     * @return self
     */
    public function setContributed(bool $contributed) : self
    {
        $this->contributed = $contributed;

        return $this;
    }

    /**
     * @return bool
     */
    public function isContributedByContributor() : ?bool
    {
        return $this->contributedByContributor;
    }

    /**
     * @param bool $contributedByContributor
     *
     * @return self
     */
    public function setContributedByContributor(bool $contributedByContributor) : self
    {
        $this->contributedByContributor = $contributedByContributor;

        return $this;
    }
}
