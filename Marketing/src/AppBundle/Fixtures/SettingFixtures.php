<?php

namespace AppBundle\Fixtures;

use AppBundle\Entity\Setting;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class SettingFixtures extends Fixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $userReportingFromYearSetting = new Setting();
        $userReportingFromYearSetting->setKey(Setting::USER_REPORTING_FROM_YEAR);
        $userReportingFromYearSetting->setValue(2018);

        $userReportingToYearSetting = new Setting();
        $userReportingToYearSetting->setKey(Setting::USER_REPORTING_TO_YEAR);
        $userReportingToYearSetting->setValue(2023);

        $manager->persist($userReportingFromYearSetting);
        $manager->persist($userReportingToYearSetting);

        $manager->flush();
    }
}
