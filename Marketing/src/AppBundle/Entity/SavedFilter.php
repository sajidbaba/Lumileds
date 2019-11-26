<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="saved_filters")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SavedFilterRepository")
 */
class SavedFilter
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
     * @var array|null
     *
     * @ORM\Column(name="contribution_filter", type="json", nullable=true)
     */
    private $contributionFilter;

    /**
     * @var array|null
     *
     * @ORM\Column(name="edit_filter", type="json", nullable=true)
     */
    private $editFilter;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

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
     * @return array|null
     */
    public function getContributionFilter(): ?array
    {
        return $this->contributionFilter;
    }

    /**
     * @param array|null $contributionFilter
     */
    public function setContributionFilter(?array $contributionFilter): void
    {
        $this->contributionFilter = $contributionFilter;
    }

    /**
     * @return array|null
     */
    public function getEditFilter(): ?array
    {
        return $this->editFilter;
    }

    /**
     * @param array|null $editFilter
     */
    public function setEditFilter(?array $editFilter): void
    {
        $this->editFilter = $editFilter;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return self
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
}
