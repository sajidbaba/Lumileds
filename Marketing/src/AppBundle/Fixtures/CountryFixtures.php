<?php

namespace AppBundle\Fixtures;

use AppBundle\Entity\Country;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class CountryFixtures extends Fixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $countries = [
            'Argentina' => 'LATAM',
            'Australia' => 'APAC',
            'Austria' => 'EMEA',
            'Belarus' => 'EMEA',
            'Belgium' => 'EMEA',
            'Bolivia' => 'LATAM',
            'Brazil' => 'LATAM',
            'Bulgaria' => 'EMEA',
            'Canada' => 'NAFTA',
            'Central America' => 'LATAM',
            'Chile' => 'LATAM',
            'China' => 'Greater China',
            'Colombia' => 'LATAM',
            'Czech & Slovakia' => 'EMEA',
            'France' => 'EMEA',
            'Germany' => 'EMEA',
            'Greece' => 'EMEA',
            'Hungary' => 'EMEA',
            'India' => 'APAC',
            'Indonesia' => 'APAC',
            'Iran' => 'EMEA',
            'Israel' => 'EMEA',
            'Italy' => 'EMEA',
            'Japan' => 'APAC',
            'Kazakhstan' => 'EMEA',
            'Korea' => 'APAC',
            'Malaysia' => 'APAC',
            'Mexico' => 'NAFTA',
            'Netherlands' => 'EMEA',
            'New Zealand' => 'APAC',
            'North Africa' => 'EMEA',
            'Oceania' => 'APAC',
            'Paraguay' => 'LATAM',
            'Peru' => 'LATAM',
            'Philippines' => 'APAC',
            'Poland' => 'EMEA',
            'Portugal' => 'EMEA',
            'RoAF' => 'EMEA',
            'RoAS' => 'APAC',
            'RoCE' => 'EMEA',
            'RoEE' => 'EMEA',
            'RoLAT' => 'LATAM',
            'Romania' => 'EMEA',
            'RoME' => 'EMEA',
            'RoMEA' => 'EMEA',
            'RoNE' => 'EMEA',
            'RoWE' => 'EMEA',
            'Russia' => 'EMEA',
            'Saudi Arabia' => 'EMEA',
            'Singapore' => 'APAC',
            'South Africa' => 'EMEA',
            'Spain' => 'EMEA',
            'Sweden' => 'EMEA',
            'Switzerland' => 'EMEA',
            'Taiwan' => 'Greater China',
            'Thailand' => 'APAC',
            'Turkey' => 'Greater China',
            'UK' => 'Greater China',
            'Ukraine' => 'Greater China',
            'USA' => 'NAFTA',
            'Venezuela' => 'LATAM',
            'Vietnam' => 'APAC',
        ];

        foreach ($countries as $countryName => $regionName) {
            $countryEntity = new Country();
            $countryEntity->setName($countryName);
            $countryEntity->setActive(true);
            $countryEntity->setRegion($this->getReference('region-'.$regionName));

            $manager->persist($countryEntity);

            $this->addReference('country-'.$countryName, $countryEntity);
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
