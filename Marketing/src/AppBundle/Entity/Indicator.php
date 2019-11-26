<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Indicator
 *
 * @ORM\Table(name="indicators")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\IndicatorRepository")
 * @ORM\Cache(usage="READ_ONLY", region="lumileds_region")
 */
class Indicator
{
    // Indicator types.
    const INDICATOR_TYPE_INPUT = 1;
    const INDICATOR_TYPE_OUTPUT = 2;
    const INDICATOR_TYPE_MIXED = 3;

    // Indicator identifiers.
    const INDICATOR_PARC = 1;
    const INDICATOR_ADDR_COEFF = 2;
    const INDICATOR_TECH_SPLIT = 3;

    const INDICATOR_ANNUAL_MILEAGE_IN_KM = 8;
    const INDICATOR_AVERAGE_SPEED_IN_KM_H = 9;
    const INDICATOR_PERCENTAGE_TIME_WITH_LIGHTS_ON = 10;
    const INDICATOR_LIFETIME_OF_BULB_IN_HOURS = 11;
    const INDICATOR_UPGRADE_TAKE_RATE = 12;
    const INDICATOR_LIFETIME_OF_BULB_IN_YEARS = 13;
    const INDICATOR_OPERATION_RATE = 14;
    const INDICATOR_ASP_LC = 18;
    const INDICATOR_ASP_USD = 19;
    const INDICATOR_PRICE_DEVELOPMENT = 20;
    const INDICATOR_LL_VOLUME_SHARE = 26;
    const INDICATOR_MARKET_VOLUME = 32;
    const INDICATOR_MARKET_VALUE_LC = 33;
    const INDICATOR_MARKET_VALUE_USD = 34;
    const INDICATOR_LL_VOLUME = 35;
    const INDICATOR_LL_SALES_USD = 36;
    const INDICATOR_LL_VALUE_SHARE = 37;
    const INDICATOR_HOURS_USAGE_PER_THOUSAND_KM = 38;
    const INDICATOR_EXCHANGE_RATE = 39;

    const START_YEAR = 2015;

    const NUMBER_OF_BULBS_CHMSL = 1.0;
    const NUMBER_OF_BULBS_CONV = 2.0;
    const NUMBER_OF_BULBS_POSL = 2.0;
    const NUMBER_OF_BULBS_TURN = 4.0;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     */
    private $id;

    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="indicators")
     */
    private $country;

    /**
     * @var Segment
     *
     * @ORM\ManyToOne(targetEntity="Segment", inversedBy="indicators")
     */
    private $segment;

    /**
     * @var Technology
     *
     * @ORM\ManyToOne(targetEntity="Technology", inversedBy="indicators")
     */
    private $technology;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="type", type="integer")
     */
    private $type;

    /**
     * @var Cell[]
     *
     * @ORM\OneToMany(targetEntity="Cell", mappedBy="indicator")
     */
    private $cells;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cells = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param int $id
     *
     * @return Indicator
     */
    public function setId($id)
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
     * Set name
     *
     * @param string $name
     *
     * @return Indicator
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
     * Set country
     *
     * @param Country $country
     *
     * @return Indicator
     */
    public function setCountry(Country $country = null)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set segment
     *
     * @param Segment $segment
     *
     * @return Indicator
     */
    public function setSegment(Segment $segment = null)
    {
        $this->segment = $segment;

        return $this;
    }

    /**
     * Get segment
     *
     * @return Segment
     */
    public function getSegment()
    {
        return $this->segment;
    }

    /**
     * Set technology
     *
     * @param Technology $technology
     *
     * @return Indicator
     */
    public function setTechnology(Technology $technology = null)
    {
        $this->technology = $technology;

        return $this;
    }

    /**
     * Get technology
     *
     * @return Technology
     */
    public function getTechnology()
    {
        return $this->technology;
    }

    /**
     * Add cell
     *
     * @param Cell $cell
     *
     * @return Indicator
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
     * Set type
     *
     * @param integer $type
     *
     * @return Indicator
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->id;
    }
}
