<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Technology
 *
 * @ORM\Table(name="technologies")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TechnologyRepository")
 * @ORM\Cache(usage="READ_ONLY", region="lumileds_region")
 */
class Technology
{
    const TECHNOLOGY_HL_HALOGEN = 1;
    const TECHNOLOGY_HL_NON_HALOGEN = 2;
    const TECHNOLOGY_HL_LED = 3;
    const TECHNOLOGY_HL_XENON = 4;
    const TECHNOLOGY_HL_LED_RF = 5;
    const TECHNOLOGY_SL_CONV = 6;
    const TECHNOLOGY_SL_DRL_CONV = 7;
    const TECHNOLOGY_SL_TURN_CONV = 8;
    const TECHNOLOGY_SL_POSL_CONV = 9;
    const TECHNOLOGY_SL_STOP_CONV = 10;
    const TECHNOLOGY_SL_CHMSL_CONV = 11;
    const TECHNOLOGY_SL_LP_CONV = 12;
    const TECHNOLOGY_SL_FF_CONV = 13;
    const TECHNOLOGY_SL_DRL = 14;
    const TECHNOLOGY_SL_TURN = 15;
    const TECHNOLOGY_SL_STOP = 16;
    const TECHNOLOGY_SL_LP = 17;
    const TECHNOLOGY_SL_CHMSL = 18;
    const TECHNOLOGY_SL_POSL = 19;
    const TECHNOLOGY_SL_FF_LED_RF = 20;
    const TECHNOLOGY_SL_FF_LED = 21;
    const TECHNOLOGY_SL_DRL_LED_RF = 22;
    const TECHNOLOGY_SL_TURN_LED_RF = 23;
    const TECHNOLOGY_SL_TURN_LED = 24;
    const TECHNOLOGY_SL_STOP_LED_RF = 25;
    const TECHNOLOGY_SL_STOP_LED = 26;
    const TECHNOLOGY_SL_BU_LED_RF = 27;
    const TECHNOLOGY_SL_LP_LED_RF = 28;
    const TECHNOLOGY_SL_LP_LED = 29;
    const TECHNOLOGY_SL_RF_LED_RF = 30;
    const TECHNOLOGY_SL_CHMSL_LED_RF = 31;
    const TECHNOLOGY_SL_CHMSL_LED = 32;
    const TECHNOLOGY_SL_TAIL_LED_RF = 33;
    const TECHNOLOGY_SL_POSL_LED_RF = 34;
    const TECHNOLOGY_SL_POSL_LED = 35;
    const TECHNOLOGY_SL_LED_RF = 36;
    const TECHNOLOGY_SL_DRL_LED = 37;
    const TECHNOLOGY_SL_HIPER = 38;
    const TECHNOLOGY_TOTAL = 39;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var Indicator[]
     *
     * @ORM\OneToMany(targetEntity="Indicator", mappedBy="technology")
     */
    private $indicators;

    /**
     * @var Cell[]
     *
     * @ORM\OneToMany(targetEntity="Cell", mappedBy="technology")
     */
    private $cells;

    public function __construct()
    {
        $this->indicators = new ArrayCollection();
        $this->cells = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param int $id
     *
     * @return Technology
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
     * @return Technology
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
     * Add indicator
     *
     * @param Indicator $indicator
     *
     * @return Technology
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
     * @return Technology
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
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->id;
    }
}
