<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="contribution_cell_modifications")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContributionCellModificationRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ContributionCellModification
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
     * @var string|null
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=true)
     */
    private $value;

    /**
     * @var Contribution
     *
     * @ORM\ManyToOne(targetEntity="Contribution", inversedBy="contributionCellModifications")
     * @ORM\JoinColumn(name="contribution_id", referencedColumnName="id", nullable=false)
     */
    private $contribution;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="contributionCellModifications")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @var Cell
     *
     * @ORM\ManyToOne(targetEntity="Cell", inversedBy="contributionCellModifications")
     * @ORM\JoinColumn(name="cell_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $cell;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     *
     * @return self
     */
    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return Contribution
     */
    public function getContribution(): ?Contribution
    {
        return $this->contribution;
    }

    /**
     * @param Contribution $contribution
     *
     * @return self
     */
    public function setContribution(Contribution $contribution): self
    {
        $this->contribution = $contribution;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return self
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

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
     * @param Cell $cell
     *
     * @return self
     */
    public function setCell(Cell $cell): self
    {
        $this->cell = $cell;

        return $this;
    }
}
