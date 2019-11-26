<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * Version
 *
 * @ORM\Table(name="versions")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VersionRepository")
 */
class Version
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var CellVersion[]
     *
     * @ORM\OneToMany(targetEntity="CellVersion", mappedBy="version", cascade={"persist"})
     */
    private $cellVersions;

    /**
     * @var boolean
     *
     * @ORM\Column(name="cycle", type="boolean")
     */
    private $cycle = false;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="created_by", type="string", length=255)
     */
    private $createdBy;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="approved_at", type="datetime", nullable=true)
     */
    private $approvedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="approved_by", type="string", length=255, nullable=true)
     */
    private $approvedBy;

    /**
     * @var string
     *
     * @ORM\Column(name="queue_hash", type="string", length=64, nullable=true)
     */
    private $queueHash;


    /**
     * Version constructor.
     */
    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->name = $this->createdAt->format('d-m-Y H:i:s');
        $this->cellVersions = new ArrayCollection();
    }

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
     * Set name
     *
     * @param string $name
     *
     * @return Version
     */
    public function setName(string $name): self
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
     * @param CellVersion[] $cellVersions
     *
     * @return Version
     */
    public function setCellVersions($cellVersions): self
    {
        $this->cellVersions = $cellVersions;

        return $this;
    }

    /**
     * @return ArrayCollection|CellVersion[]
     */
    public function getCellVersions()
    {
        return $this->cellVersions;
    }

    /**
     * Add cell version
     *
     * @param CellVersion $cellVersion
     *
     * @return Version
     */
    public function addCellVersion(CellVersion $cellVersion): self
    {
        $this->cellVersions->add($cellVersion);
        $cellVersion->setVersion($this);

        return $this;
    }

    /**
     * @param bool $cycle
     *
     * @return Version
     */
    public function setCycle($cycle): self
    {
        $this->cycle = $cycle;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCycle(): bool
    {
        return $this->cycle;
    }

    /**
     * Get created at
     *
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set created by user
     *
     * @param string $createdBy
     *
     * @return Version
     */
    public function setCreatedBy($createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set approvedAt
     *
     * @param DateTime $approvedAt
     *
     * @return Version
     */
    public function setApprovedAt(DateTime $approvedAt): self
    {
        $this->approvedAt = $approvedAt;

        return $this;
    }

    /**
     * Get approvedAt
     *
     * @return DateTime
     */
    public function getApprovedAt(): ?DateTime
    {
        return $this->approvedAt;
    }

    /**
     * Set approvedBy
     *
     * @param string $approvedBy
     *
     * @return Version
     */
    public function setApprovedBy($approvedBy): self
    {
        $this->approvedBy = $approvedBy;

        return $this;
    }

    /**
     * Get approvedBy
     *
     * @return string
     */
    public function getApprovedBy()
    {
        return $this->approvedBy;
    }

    /**
     * Set queue hash
     *
     * @param string|null $queueHash
     *
     * @return Version
     */
    public function setQueueHash(?string $queueHash): self
    {
        $this->queueHash = $queueHash;

        return $this;
    }

    /**
     * Get queue hash
     *
     * @return string|null
     */
    public function getQueueHash(): ?string
    {
        return $this->queueHash;
    }

}

