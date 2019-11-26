<?php

namespace AppBundle\Model;

use JMS\Serializer\Annotation as JMS;

class Row
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @JMS\Type("string")
     * @var string
     */
    protected $market;

    /**
     * @JMS\Type("string")
     * @var string
     */
    protected $segment;

    /**
     * @JMS\Type("string")
     * @var string
     */
    protected $indicator;

    /**
     * @JMS\Type("string")
     * @var string
     */
    protected $technology;

    /**
     * @var array
     */
    protected $cells = [];

    /**
     * @JMS\Type("bool")
     *
     * @var bool
     */
    protected $approved = false;

    /**
     * Show if this row has cells with contribution made by contributor
     *
     * @var bool
     *
     * @JMS\Type("bool")
     */
    private $isContributedByContributor;

    /**
     * Get key generated from market, segment, indicator, technology names
     */
    public function getKey(): string
    {
        return implode(
            '-',
            [
                $this->getMarket(),
                $this->getSegment(),
                $this->getIndicator(),
                $this->getTechnology(),
            ]
        );
    }

    /**
     * @return string
     */
    public function getMarket(): string
    {
        return $this->market;
    }

    /**
     * @param string $market
     *
     * @return Row
     */
    public function setMarket(string $market): self
    {
        $this->market = $market;

        return $this;
    }

    /**
     * @return string
     */
    public function getSegment(): string
    {
        return $this->segment;
    }

    /**
     * @param string $segment
     *
     * @return Row
     */
    public function setSegment(string $segment): self
    {
        $this->segment = $segment;

        return $this;
    }

    /**
     * @return string
     */
    public function getIndicator(): string
    {
        return $this->indicator;
    }

    /**
     * @param string $indicator
     *
     * @return Row
     */
    public function setIndicator(string $indicator): self
    {
        $this->indicator = $indicator;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTechnology(): ?string
    {
        return $this->technology;
    }

    /**
     * @param string|null $technology
     *
     * @return Row
     */
    public function setTechnology(?string $technology): self
    {
        $this->technology = $technology;

        return $this;
    }

    /**
     * @JMS\Type("array")
     *
     * @return Cell[]
     */
    public function getCells()
    {
        return $this->cells;
    }

    /**
     * @param Cell $cell
     *
     * @return Row
     */
    public function addCell($cell): self
    {
        $this->cells[] = $cell;

        return $this;
    }

    /**
     * @param bool $isContributedByContributor
     *
     * @return self
     */
    public function setIsContributedByContributor(bool $isContributedByContributor): self
    {
        $this->isContributedByContributor = $isContributedByContributor;

        return $this;
    }

    /**
     * @return bool
     */
    public function isContributedByContributor(): bool
    {
        return $this->isContributedByContributor;
    }

    /**
     * @return bool
     */
    public function isApproved() : ?bool
    {
        return $this->approved;
    }

    /**
     * @param bool $approved
     *
     * @return self
     */
    public function setApproved(bool $approved) : self
    {
        $this->approved = $approved;

        return $this;
    }
}
