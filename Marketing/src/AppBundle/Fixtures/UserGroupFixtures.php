<?php

namespace AppBundle\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserGroupFixtures extends Fixture implements ContainerAwareInterface, OrderedFixtureInterface
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
        $groupManager = $this->container->get('fos_user.group_manager');

        $viewerGroup = $groupManager->createGroup('viewer');
        $viewerGroup->addRole('ROLE_VIEWER');
        $groupManager->updateGroup($viewerGroup);
        $this->addReference('user-group-viewer', $viewerGroup);

        $contributorGroup = $groupManager->createGroup('contributor');
        $contributorGroup->addRole('ROLE_CONTRIBUTOR');
        $groupManager->updateGroup($contributorGroup);
        $this->addReference('user-group-contributor', $contributorGroup);

        $adminGroup = $groupManager->createGroup('admin');
        $adminGroup->addRole('ROLE_ADMIN');
        $groupManager->updateGroup($adminGroup);
        $this->addReference('user-group-admin', $adminGroup);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 2;
    }
}
