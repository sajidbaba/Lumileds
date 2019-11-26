<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="orders")
 */
class Order
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
     * @var Indicator
     *
     * @ORM\ManyToOne(targetEntity="Indicator")
     * @ORM\JoinColumn(name="indicator_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $indicator;

    /**
     * @var Technology
     *
     * @ORM\ManyToOne(targetEntity="Technology")
     * @ORM\JoinColumn(name="technology_id", referencedColumnName="id")
     */
    private $technology;

    /**
     * @var int
     *
     * @ORM\Column(name="priority", type="integer")
     */
    private $priority;

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
     * Set technology
     *
     * @param Technology $technology
     *
     * @return Order
     */
    public function setTechnology(Technology $technology = null): self
    {
        $this->technology = $technology;

        return $this;
    }

    /**
     * Get technology
     *
     * @return Technology
     */
    public function getTechnology(): ?Technology
    {
        return $this->technology;
    }

    /**
     * Set indicator
     *
     * @param Indicator $indicator
     *
     * @return Order
     */
    public function setIndicator(Indicator $indicator = null): self
    {
        $this->indicator = $indicator;

        return $this;
    }

    /**
     * Get indicator
     *
     * @return Indicator
     */
    public function getIndicator(): ?Indicator
    {
        return $this->indicator;
    }

    /**
     * @param int $priority
     *
     * @return Order
     */
    public function setPriority(int $priority): Order
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }
}

