<?php

namespace Tests;

use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Liip\FunctionalTestBundle\Test\WebTestCase as LiipWebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Bundle\FrameworkBundle\Client;
use AppBundle\Fixtures\CountryFixtures;
use AppBundle\Fixtures\IndicatorFixtures;
use AppBundle\Fixtures\OrderFixtures;
use AppBundle\Fixtures\RegionFixtures;
use AppBundle\Fixtures\SegmentFixtures;
use AppBundle\Fixtures\TechnologyFixtures;
use AppBundle\Fixtures\UserFixtures;
use AppBundle\Fixtures\UserGroupFixtures;

class WebTestCase extends LiipWebTestCase
{
    /** @var bool */
    protected static $dbInitialized = false;

    /** @var Client */
    protected $client = null;

    /** @var ContainerInterface */
    protected $container;

    /** @var EntityManager */
    protected $em;

    public function setUp()
    {
        $this->client = $this->makeClient();
        $this->container = static::$kernel->getContainer();
        $this->em = $this->container->get('doctrine.orm.default_entity_manager');

        $this->setupDatabase();
    }

    /**
     * @param string $user
     * @param array $roles
     */
    protected function logIn($user = 'admin', $roles = ['ROLE_ADMIN'])
    {
        $session = $this->client->getContainer()->get('session');

        // the firewall context defaults to the firewall name
        $firewallContext = 'main';

        $token = new UsernamePasswordToken($user, null, $firewallContext, $roles);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    protected function setupDatabase()
    {
        if (!static::$dbInitialized) {
            $this->createDatabase();
            $this->recreateSchema();

            static::$dbInitialized = true;
        }
    }

    private function recreateSchema()
    {
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    private function createDatabase()
    {
        $application = new Application(static::$kernel);
        $command = new CreateDatabaseDoctrineCommand();
        $application->add($command);
        $input = new ArrayInput(array(
            'command' => 'doctrine:database:create',
        ));
        $command->run($input, new NullOutput());
    }

    protected function loadAllFixtures()
    {
        $this->loadFixtures([
            RegionFixtures::class,
            UserGroupFixtures::class,
            CountryFixtures::class,
            TechnologyFixtures::class,
            UserFixtures::class,
            SegmentFixtures::class,
            IndicatorFixtures::class,
            OrderFixtures::class,
        ]);
    }
}
