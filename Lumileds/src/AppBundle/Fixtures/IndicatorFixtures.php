<?php

namespace AppBundle\Fixtures;

use AppBundle\Entity\Indicator;
use AppBundle\Indicators\AbstractIndicator;
use AppBundle\Indicators\Input\InputIndicatorInterface;
use AppBundle\Indicators\Output\OutputIndicatorInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class IndicatorFixtures extends Fixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var AbstractIndicator[] $indicators */
        $indicators = $this->container->get('indicator_registry')
            ->getRegistry();

        foreach ($indicators as $indicator) {
            $indicatorEntity = new Indicator();
            $indicatorEntity->setId($indicator->getId());
            $indicatorEntity->setName($indicator->getName());

            $isInput = $indicator instanceof InputIndicatorInterface;
            $isOutput = $indicator instanceof OutputIndicatorInterface;
            $isBoth = $isInput && $isOutput;

            if ($isBoth) {
                $indicatorEntity->setType(Indicator::INDICATOR_TYPE_MIXED);
            }
            elseif ($isInput) {
                $indicatorEntity->setType(Indicator::INDICATOR_TYPE_INPUT);
            }
            elseif ($isOutput) {
                $indicatorEntity->setType(Indicator::INDICATOR_TYPE_OUTPUT);
            }
            else {
                throw new \Exception('Unknown indicator type.');
            }

            if ($technology = $indicator->getTechnology()) {
                $indicatorEntity->setTechnology($this->getReference('technology-'.$technology));
            }

            $manager->persist($indicatorEntity);

            $this->addReference('indicator-'.$indicator->getName().$indicator->getTechnology(), $indicatorEntity);
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 4;
    }
}
