<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cell Version
 *
 * @ORM\Table(name="cell_versions")
 * @ORM\Entity()
 */
class CellVersion
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
     * @var Version
     *
     * @ORM\ManyToOne(targetEntity="Version", inversedBy="cellVersions")
     * @ORM\JoinColumn(name="version_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $version;

    /**
     * @var Cell
     *
     * @ORM\ManyToOne(targetEntity="Cell", inversedBy="cellVersions")
     * @ORM\JoinColumn(name="cell_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $cell;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=true)
     */
    private $value;

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param Version $version
     *
     * @return CellVersion
     */
    public function setVersion($version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getVersion(): ?Version
    {
        return $this->version;
    }

    /**
     * @param Cell $cell
     *
     * @return CellVersion
     */
    public function setCell(Cell $cell): self
    {
        $this->cell = $cell;

        return $this;
    }

    /**
     * @return Cell
     */
    public function getCell(): Cell
    {
        return $this->cell;
    }

    /**
     * Set value
     *
     * @param string|null $value
     *
     * @return CellVersion
     */
    public function setValue(string $value = null): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }
}

