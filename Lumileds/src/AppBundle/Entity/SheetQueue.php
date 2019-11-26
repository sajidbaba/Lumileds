<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SheetQueue
 *
 * @ORM\Table(name="sheet_queues")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SheetQueueRepository")
 */
class SheetQueue
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
     * @ORM\Column(name="filepath", type="string", length=255)
     */
    private $filePath;

    /**
     * @var boolean
     *
     * @ORM\Column(name="state", type="boolean")
     */
    private $processed;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="queues")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="hash", type="string", length=64)
     */
    private $hash;

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
     * Set filePath
     *
     * @param string $filePath
     *
     * @return SheetQueue
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Get filePath
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * Set processed
     *
     * @param boolean $processed
     *
     * @return SheetQueue
     */
    public function setProcessed($processed)
    {
        $this->processed = $processed;

        return $this;
    }

    /**
     * Get processed
     *
     * @return boolean
     */
    public function getProcessed()
    {
        return $this->processed;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return SheetQueue
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set hash
     *
     * @param string $hash
     *
     * @return SheetQueue
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }
}
