<?php

namespace AppBundle\Entity;

use AppBundle\Indicators\IndicatorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Cell
 *
 * @ORM\Table(
 *     name="cells",
 *     indexes={
 *         @ORM\Index(name="cell_calculate", columns={"country_id", "segment_id", "indicator_id", "technology_id", "year"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CellRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="lumileds_region")
 */
class Cell
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Country
     *
     * @ORM\Cache()
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="cells")
     */
    private $country;

    /**
     * @var Segment
     *
     * @ORM\Cache()
     * @ORM\ManyToOne(targetEntity="Segment", inversedBy="cells")
     */
    private $segment;

    /**
     * @var Indicator
     *
     * @ORM\Cache()
     * @ORM\ManyToOne(targetEntity="Indicator", inversedBy="cells")
     * @ORM\JoinColumn(name="indicator_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $indicator;

    /**
     * @var Technology
     *
     * @ORM\Cache()
     * @ORM\ManyToOne(targetEntity="Technology", inversedBy="cells")
     */
    private $technology;

    /**
     * @var string
     *
     * @ORM\Column(name="year", type="string", length=4, nullable=false)
     */
    private $year;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=true)
     */
    private $value;

    /**
     * @var CellError|null
     *
     * @ORM\Cache()
     * @ORM\OneToOne(targetEntity="CellError", mappedBy="cell")
     */
    private $errorLog;

    /**
     * @var CellVersion[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="CellVersion", mappedBy="cell")
     */
    private $cellVersions;

    /**
     * @var IndicatorInterface
     */
    private $indicatorClass;

    /**
     * @var ContributionCellModification[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ContributionCellModification", mappedBy="cell")
     */
    private $contributionCellModifications;

    /**
     * Cell constructor.
     */
    public function __construct()
    {
        $this->cellVersions = new ArrayCollection();
        $this->contributionCellModifications = new ArrayCollection();
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
     * @param Country $country
     *
     * @return Cell
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
     * @return Cell
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
     * Set indicator
     *
     * @param Indicator $indicator
     *
     * @return Cell
     */
    public function setIndicator(Indicator $indicator = null)
    {
        $this->indicator = $indicator;

        return $this;
    }

    /**
     * Get indicator
     *
     * @return Indicator
     */
    public function getIndicator()
    {
        return $this->indicator;
    }

    /**
     * Set technology
     *
     * @param Technology $technology
     *
     * @return Cell
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
     * Set error log
     *
     * @param CellError $errorLog
     *
     * @return Cell
     */
    public function setErrorLog(?CellError $errorLog): Cell
    {
        $this->errorLog = $errorLog;

        return $this;
    }

    /**
     * Get error log
     *
     * @return CellError
     */
    public function getErrorLog(): ?CellError
    {
        return $this->errorLog;
    }

    /**
     * Get cell versions
     *
     * @return CellVersion[]
     */
    public function getCellVersions(): array
    {
        return $this->cellVersions;
    }

    /**
     * Add cell version
     *
     * @param CellVersion $cellVersion
     *
     * @return Cell
     */
    public function addCellVersion(CellVersion $cellVersion): self
    {
        if (!$this->cellVersions->contains($cellVersion)) {
            $this->cellVersions->add($cellVersion);
        }

        return $this;
    }

    /**
     * Remove cell version
     *
     * @param CellVersion $cellVersion
     *
     * @return Cell
     */
    public function removeCellVersion(CellVersion $cellVersion): self
    {
        $this->cellVersions->removeElement($cellVersion);

        return $this;
    }

    /**
     * Returns the attached indicator class.
     *
     * @return IndicatorInterface
     *   Indicator class.
     */
    public function getIndicatorClass(): IndicatorInterface
    {
        return $this->indicatorClass;
    }

    /**
     * Attaches the indicator class.
     *
     * @param IndicatorInterface $indicator
     *   Indicator class.
     */
    public function setIndicatorClass(IndicatorInterface $indicator)
    {
        $this->indicatorClass = $indicator;
    }

    /**
     * @return ContributionCellModification[]|ArrayCollection
     */
    public function getContributionCellModifications()
    {
        return $this->contributionCellModifications;
    }

    /**
     * @param ContributionCellModification $ContributionCellModification
     *
     * @return self
     */
    public function addContributionCellModification(ContributionCellModification $ContributionCellModification): self
    {
        if (!$this->contributionCellModifications->contains($ContributionCellModification)) {
            $this->contributionCellModifications->add($ContributionCellModification);
        }

        return $this;
    }

    /**
     * @param ContributionCellModification $ContributionCellModification
     *
     * @return self
     */
    public function removeContributionCellModification(ContributionCellModification $ContributionCellModification): self
    {
        $this->contributionCellModifications->removeElement($ContributionCellModification);

        return $this;
    }
}
