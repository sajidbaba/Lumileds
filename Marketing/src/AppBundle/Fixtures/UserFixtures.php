<?php

namespace AppBundle\Fixtures;

use AppBundle\Entity\Country;
use AppBundle\Entity\Group;
use AppBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserFixtures extends Fixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /** @var ContainerInterface */
    protected $container;

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
        $userManager = $this->container->get('fos_user.user_manager');

        /** @var Country $country */
        $country = $this->getReference('country-Japan');

        /** @var Group $viewerGroup */
        $viewerGroup = $this->getReference('user-group-viewer');
        /** @var Group $contributorGroup */
        $contributorGroup = $this->getReference('user-group-contributor');
        /** @var Group $adminGroup */
        $adminGroup = $this->getReference('user-group-admin');

        /** @var User $viewer */
        $viewer = $userManager->createUser();
        $viewer->setUsername('viewer');
        $viewer->setEmail('viewer@lumileds.com');
        $viewer->setPlainPassword('viewer');
        $viewer->setEnabled(true);
        $viewer->setGroup($viewerGroup);
        $userManager->updateUser($viewer);

        /** @var User $contributor */
        $contributor = $userManager->createUser();
        $contributor->setUsername('contributor');
        $contributor->setEmail('contributor@lumileds.com');
        $contributor->setPlainPassword('contributor');
        $contributor->setEnabled(true);
        $contributor->setGroup($contributorGroup);
        $contributor->addCountry($country);
        $userManager->updateUser($contributor);

        /** @var User $admin */
        $admin = $userManager->createUser();
        $admin->setUsername('admin');
        $admin->setEmail('admin@lumileds.com');
        $admin->setPlainPassword('admin');
        $admin->setEnabled(true);
        $admin->setGroup($adminGroup);
        $userManager->updateUser($admin);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 3;
    }
}
