<?php

namespace AppBundle\Indicators;

use AppBundle\Entity\Cell;
use AppBundle\Exception\NotFoundDependencyException;
use AppBundle\Indicators\Output\OutputIndicatorInterface;
use AppBundle\Repository\CellRepository;
use Doctrine\Common\Persistence\ObjectManager;

abstract class AbstractIndicator implements IndicatorInterface
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $technology = '';

    /**
     * @var string
     */
    protected $segment = '';

    /**
     * @var string
     */
    protected $market = '';

    /**
     * @var array
     */
    private $cache = [];

    /**
     * Returns indicator name.
     *
     * @return string
     *   Indicator name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns technology name attached to this indicator.
     *
     * @return string
     *   Technology name.
     */
    public function getTechnology(): string
    {
        return $this->technology;
    }

    /**
     * Returns segment attached to this indicator.
     *
     * @return string
     *   Segment name.
     */
    public function getSegment(): string
    {
        return $this->segment;
    }

    /**
     * Returns market attached to this indicator.
     *
     * @return string
     *   Market name.
     */
    public function getMarket(): string
    {
        return $this->market;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrecision(): int
    {
        return 0;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $chunks = [
            $this->getMarket(),
            $this->getSegment(),
            $this->getName(),
            $this->getTechnology(),
        ];

        return implode('', $chunks);
    }

    /**
     * {@inheritdoc}
     */
    public function isEditable(Cell $cell): bool
    {
        return true;
    }

    /**
     * Calculates dependency indicators.
     *
     * @param array $dependencies
     *   A set of dependencies.
     * @param Cell $cell
     *   Cell that must be calculated.
     * @param ObjectManager $em
     *   Doctrine object manager.
     * @param bool $forceRecalculation
     *   Force calculation without cache
     * @param bool $isUpload
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function resolveDependencies(
        array $dependencies,
        Cell $cell,
        ObjectManager $em,
        bool $forceRecalculation = false,
        bool $isUpload = false
    ) {
        /** @var CellRepository $cellRepository */
        $cellRepository = $em->getRepository(Cell::class);

        foreach ($dependencies as $dependency) {
            $market = $cell->getCountry()->getId();
            $segment = $cell->getSegment()->getId();
            $indicator = $dependency['indicator'];
            $technology = $dependency['technology'] ?: $cell->getTechnology()->getId();
            $year = isset($dependency['year']) ? $dependency['year'] : $cell->getYear();

            /** @var Cell $dependencyEntity */
            $dependencyEntity = $cellRepository->findOneByIndicatorTechnologyAndYear(
                $market,
                $segment,
                $indicator,
                $technology,
                $year
            );

            $cid = $market.'-'.$segment.'-'.$indicator.'-'.$technology.'-'.$year;

            if (!isset($this->cache[$cid]) || $forceRecalculation) {
                $dependencyValue = null;
                if ($dependencyEntity && $dependencyEntity->getIndicatorClass() instanceof OutputIndicatorInterface) {
                    $dependencyValue = $dependencyEntity->getIndicatorClass()->getCalculatedValue($dependencyEntity, $em, $forceRecalculation, $isUpload);
                }

                $this->cache[$cid] = $dependencyValue;
            }

            if ($dependencyEntity) {
                $dependencyEntity->setValue($this->cache[$cid]);
            } else {
                throw new NotFoundDependencyException(
                    sprintf(
                        'Dependency (indicator: %s, technology: %s, segment: %s) for indicator "%s" is not found',
                        $indicator,
                        $technology,
                        $segment,
                        $cell->getIndicator()->getName()
                    ),
                    $indicator,
                    $technology,
                    $segment,
                    $cell->getCountry()->getName()
                );
            }
        }
    }
}
