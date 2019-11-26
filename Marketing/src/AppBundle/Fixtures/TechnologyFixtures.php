<?php

namespace AppBundle\Fixtures;

use AppBundle\Entity\Technology;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TechnologyFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $technologies = [
            Technology::TECHNOLOGY_HL_HALOGEN => 'HL Halogen',
            Technology::TECHNOLOGY_HL_NON_HALOGEN => 'HL Non-Halogen',
            Technology::TECHNOLOGY_HL_XENON => 'HL Xenon',
            Technology::TECHNOLOGY_HL_LED => 'HL LED',
            Technology::TECHNOLOGY_SL_CONV => 'SL Conventional',
            Technology::TECHNOLOGY_SL_DRL_CONV => 'SL DRL Conventional',
            Technology::TECHNOLOGY_SL_TURN_CONV => 'SL Turn Conventional',
            Technology::TECHNOLOGY_SL_POSL_CONV => 'SL PosL Conventional',
            Technology::TECHNOLOGY_SL_STOP_CONV => 'SL Stop Conventional',
            Technology::TECHNOLOGY_SL_CHMSL_CONV => 'SL CHMSL Conventional',
            Technology::TECHNOLOGY_SL_LP_CONV => 'SL LP Conventional',
            Technology::TECHNOLOGY_SL_FF_CONV => 'SL FF Conventional',
            Technology::TECHNOLOGY_HL_LED_RF => 'HL LED RF',
            Technology::TECHNOLOGY_SL_DRL => 'SL DRL',
            Technology::TECHNOLOGY_SL_TURN => 'SL Turn',
            Technology::TECHNOLOGY_SL_STOP => 'SL Stop',
            Technology::TECHNOLOGY_SL_LP => 'SL LP',
            Technology::TECHNOLOGY_SL_CHMSL => 'SL CHMSL',
            Technology::TECHNOLOGY_SL_POSL => 'SL PosL',
            Technology::TECHNOLOGY_SL_FF_LED_RF => 'SL FF LED RF',
            Technology::TECHNOLOGY_SL_FF_LED => 'SL FF LED',
            Technology::TECHNOLOGY_SL_DRL_LED_RF => 'SL DRL LED RF',
            Technology::TECHNOLOGY_SL_TURN_LED_RF => 'SL Turn LED RF',
            Technology::TECHNOLOGY_SL_TURN_LED => 'SL Turn LED',
            Technology::TECHNOLOGY_SL_STOP_LED_RF => 'SL Stop LED RF',
            Technology::TECHNOLOGY_SL_STOP_LED => 'SL Stop LED',
            Technology::TECHNOLOGY_SL_BU_LED_RF => 'SL BU LED RF',
            Technology::TECHNOLOGY_SL_LP_LED_RF => 'SL LP LED RF',
            Technology::TECHNOLOGY_SL_LP_LED => 'SL LP LED',
            Technology::TECHNOLOGY_SL_RF_LED_RF => 'SL RF LED RF',
            Technology::TECHNOLOGY_SL_CHMSL_LED_RF => 'SL CHMSL LED RF',
            Technology::TECHNOLOGY_SL_CHMSL_LED => 'SL CHMSL LED',
            Technology::TECHNOLOGY_SL_TAIL_LED_RF => 'SL Tail LED RF',
            Technology::TECHNOLOGY_SL_POSL_LED_RF => 'SL PosL LED RF',
            Technology::TECHNOLOGY_SL_POSL_LED => 'SL PosL LED',
            Technology::TECHNOLOGY_SL_LED_RF => 'SL LED RF',
            Technology::TECHNOLOGY_SL_DRL_LED => 'SL DRL LED',
            Technology::TECHNOLOGY_SL_HIPER => 'SL HiPer',
            Technology::TECHNOLOGY_TOTAL => 'Total',
        ];

        foreach ($technologies as $id => $technologyName) {
            $technologyEntity = new Technology();
            $technologyEntity->setId($id);
            $technologyEntity->setName($technologyName);

            $manager->persist($technologyEntity);

            $this->addReference('technology-'.$technologyName, $technologyEntity);
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 2;
    }
}
