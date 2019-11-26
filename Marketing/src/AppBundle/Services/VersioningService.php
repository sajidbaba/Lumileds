<?php

namespace AppBundle\Services;

use AppBundle\Entity\Cell;
use AppBundle\Entity\CellVersion;
use AppBundle\Entity\User;
use AppBundle\Entity\Version;
use AppBundle\Repository\VersionRepository;
use Doctrine\ORM\EntityManagerInterface;

class VersioningService
{
    /**
     * @var array
     */
    private $cells = [];

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var VersionRepository
     */
    private $repo;

    /**
     * @var Version
     */
    private $version;

    /**
     * @var string
     */
    private $queueHash;

    /**
     * VersioningService constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->repo = $em->getRepository(Version::class);
    }

    /**
     * Add cell version with old/new values
     *
     * @param int $id
     * @param string|null $old
     * @param string|null $new
     */
    public function trackCell(int $id, ?string $old, ?string $new)
    {
        if ($old == $new) {
            return;
        }

        $this->cells[$id] = [
            'old' => $old,
            'new' => $new,
        ];
    }

    /**
     * Add cell version with new value
     *
     * @param int $id
     * @param string|null $value
     */
    public function trackCellNewValue(int $id, string $value = null)
    {
        if (!array_key_exists($id, $this->cells)) {
            $this->cells[$id]['old'] = null;
        }

        $this->cells[$id]['new'] = $value;
    }

    /**
     * Add cell version with old value
     *
     * @param int $id
     * @param string|null $value
     */
    public function trackCellOldValue(int $id, string $value = null)
    {
        if (!array_key_exists($id, $this->cells)) {
            $this->cells[$id]['new'] = null;
        }

        $this->cells[$id]['old'] = $value;
    }

    /**
     * Init version based on queue hash
     *
     * @param User $user
     * @param string|null $versionName
     * @param string|null $queueHash
     *
     * @throws \Exception
     */
    public function initVersion(User $user, string $versionName = null, string $queueHash = null): void
    {
        $this->queueHash = $queueHash;

        if ($queueHash) {
            $this->version = $this->repo->findOneBy(['queueHash' => $queueHash]);
        }

        if (!$this->version) {
            $this->version = (new Version())
                ->setName($versionName ?? (new \DateTime())->format('d-m-Y H:i:s'))
                ->setCycle($versionName ? true : false)
                ->setQueueHash($this->queueHash)
                ->setCreatedBy($user->getBlame());

            $this->em->persist($this->version);
            $this->em->flush();
        }
    }

    /**
     * Create new version if has aggregated cells
     *
     * @return void
     */
    public function createVersion(): void
    {
        if (!$this->hasTrackedCells()) {
            return;
        }

        foreach ($this->cells as $id => $values) {
            /** @var Cell $cell */
            $cell = $this->em->getReference(Cell::class, $id);

            $cellVersion = (new CellVersion())
                ->setCell($cell)
                ->setValue($values['old']);

            $this->version->addCellVersion($cellVersion);
            $this->em->persist($cellVersion);
        }

        $this->clearTrackedCells();

        $this->em->persist($this->version);
        $this->em->flush();
    }

    /**
     * Filter and check aggregated cells
     *
     * @return bool
     */
    public function hasTrackedCells(): bool
    {
        $this->filterTrackedCells();

        return count($this->cells) > 0;
    }

    /**
     * Clear aggregated cells after creating version
     *
     * @return void
     */
    private function clearTrackedCells(): void
    {
        $this->cells = [];
    }

    /**
     * Remove unchanged cells
     *
     * @return void
     */
    private function filterTrackedCells(): void
    {
        $this->cells = array_filter($this->cells, function ($pair) {
            return $pair['new'] != $pair['old'] && $pair['old'] !== null;
        });
    }
}
