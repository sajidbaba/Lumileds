<?php

namespace AppBundle\Fixtures;

use AppBundle\Entity\Region;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class RegionFixtures extends Fixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $regions = [
            'LATAM',
            'EMEA',
            'NAFTA',
            'Greater China',
            'APAC'
        ];

        foreach ($regions as $region) {
            $regionEntity = new Region();
            $regionEntity->setName($region);

            $manager->persist($regionEntity);
            $this->addReference('region-'.$region, $regionEntity);
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }
}
