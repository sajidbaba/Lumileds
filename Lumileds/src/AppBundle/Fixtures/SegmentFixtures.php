<?php

namespace AppBundle\Fixtures;

use AppBundle\Entity\Segment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class SegmentFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $segments = [
            Segment::SEGMENT_LV => 'LV',
            Segment::SEGMENT_HV => 'HV',
            Segment::SEGMENT_2W => '2W',
        ];

        foreach ($segments as $id => $segmentName) {
            $segmentEntity = new Segment();
            $segmentEntity->setId($id);
            $segmentEntity->setName($segmentName);

            $manager->persist($segmentEntity);

            $this->addReference('segment-'.$segmentName, $segmentEntity);
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 3;
    }
}
