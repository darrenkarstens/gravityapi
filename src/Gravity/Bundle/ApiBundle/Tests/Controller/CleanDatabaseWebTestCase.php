<?php

namespace Gravity\Bundle\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * A class for creating web test cases that are run on a clean database. Each 
 * time the test suite is run the test database is dropped and re-created.
 */
abstract class CleanDatabaseWebTestCase extends WebTestCase {
	
    private static $application;
	private static $databaseCleared = false;
	
	/** @var \Symfony\Bundle\FrameworkBundle\Client */
	protected $client;
	
	public function setUp() {
		// recreate the test database to remove any old test data
		if (!self::$databaseCleared) {
			self::runCommand('doctrine:database:drop --force --env=test');
			// need to drop the connection as the current one is pointing to the deleted database
			$con = self::$application->getKernel()->getContainer()->get('doctrine')->getConnection();
			if ($con->isConnected()) {
				$con->close();
			}
			self::runCommand('doctrine:database:create --env=test');
			self::runCommand('doctrine:schema:update --force --env=test');
			self::$databaseCleared = true;
		}
		if ($this->client == null) {
			$this->client = static::createClient();
		}
	}
	
    protected static function runCommand($command) {
        $command = sprintf('%s --quiet', $command);
//		$command = sprintf('%s', $command);
        return self::getApplication()->run(new \Symfony\Component\Console\Input\StringInput($command));
    }

    protected static function getApplication() {
        if (null === self::$application) {
            $client = static::createClient();
            self::$application = new \Symfony\Bundle\FrameworkBundle\Console\Application($client->getKernel());
            self::$application->setAutoExit(false);
        }
        return self::$application;
    }
}
