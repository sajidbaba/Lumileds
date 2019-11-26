<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="cell_errors")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CellErrorRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="lumileds_region")
 */
class CellError
{
    const TYPE_ERROR = 0;
    const TYPE_WARNING = 1;
    const TYPE_FILE_ERROR = 2;

    const TYPES = [
        self::TYPE_ERROR,
        self::TYPE_WARNING,
        self::TYPE_FILE_ERROR,
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Cell
     *
     * @ORM\OneToOne(targetEntity="Cell", inversedBy="errorLog")
     * @ORM\JoinColumn(name="cell_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $cell;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    private $message;

    /**
     * @var int
     *
     * @ORM\Column(name="type", type="integer")
     */
    private $type;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Cell $cell
     *
     * @return CellError
     */
    public function setCell(Cell $cell): CellError
    {
        $this->cell = $cell;

        return $this;
    }

    /**
     * @return Cell
     */
    public function getCell(): ?Cell
    {
        return $this->cell;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return CellError
     */
    public function setMessage($message): CellError
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     *
     * @return self
     */
    public function setType(int $type): CellError
    {
        if (!in_array($type, self::TYPES, true)) {
            throw new \InvalidArgumentException(sprintf('Unknown type "%s"', $type));
        }

        $this->type = $type;

        return $this;
    }
}
