<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="contributions")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContributionRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @JMS\ExclusionPolicy("all")
 */
class Contribution
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @JMS\Expose()
     * @JMS\Type("int")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    private $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     */
    private $createdAt;

    /**
     * @var ContributionCountryRequest
     *
     * @ORM\ManyToOne(targetEntity="ContributionCountryRequest", inversedBy="contributions")
     * @ORM\JoinColumn(name="contribution_country_request_id", referencedColumnName="id", nullable=false)
     */
    private $contributionCountryRequest;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="contributions")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     *
     * @JMS\Expose()
     */
    private $user;

    /**
     * @var ContributionCellModification[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ContributionCellModification", mappedBy="contribution")
     */
    private $contributionCellModifications;

    public function __construct()
    {
        $this->contributionCellModifications = new ArrayCollection();
    }

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
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     *
     * @return self
     */
    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return self
     */
    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return ContributionCountryRequest
     */
    public function getContributionCountryRequest(): ?ContributionCountryRequest
    {
        return $this->contributionCountryRequest;
    }

    /**
     * @param ContributionCountryRequest $contributionCountryRequest
     *
     * @return self
     */
    public function setContributionCountryRequest(ContributionCountryRequest $contributionCountryRequest): self
    {
        $this->contributionCountryRequest = $contributionCountryRequest;

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
     * @ORM\PrePersist
     *
     * @return void
     */
    public function prePersist(): void
    {
        $this->createdAt = new \DateTime();
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
