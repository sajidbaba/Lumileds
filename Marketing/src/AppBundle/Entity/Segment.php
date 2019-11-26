<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Segments
 *
 * @ORM\Table(name="segments")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SegmentRepository")
 * @ORM\Cache(usage="READ_ONLY", region="lumileds_region")
 */
class Segment
{
    const SEGMENT_LV = 1;
    const SEGMENT_HV = 2;
    const SEGMENT_2W = 3;
    const SEGMENTS = [
        self::SEGMENT_LV,
        self::SEGMENT_HV,
        self::SEGMENT_2W,
    ];

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
     * @ORM\OneToMany(targetEntity="Indicator", mappedBy="segment")
     */
    private $indicators;

    /**
     * @var Cell[]
     *
     * @ORM\OneToMany(targetEntity="Cell", mappedBy="segment")
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
     * @return Segment
     */
    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Segment
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Add indicator
     *
     * @param Indicator $indicator
     *
     * @return Segment
     */
    public function addIndicator(Indicator $indicator): self
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
    public function removeIndicator(Indicator $indicator): void
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
     * @return Segment
     */
    public function addCell(Cell $cell): self
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
    public function removeCell(Cell $cell): void
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
