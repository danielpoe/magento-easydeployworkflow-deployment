<?php

use EasyDeployWorkflows\Workflows\Application;

require_once EASYDEPLOY_WORKFLOW_ROOT . 'Classes/Autoloader.php';

class ArchivedApplicationConfigurationTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var EasyDeployWorkflows\Workflows\Application\SimpleApplicationConfiguration
	 */
	protected $configuration;

	/**
	 * @return void
	 */
	public function setUp() {
		$this->configuration = new EasyDeployWorkflows\Workflows\Application\StandardApplicationConfiguration();
	}

	/**
	 * @test
	 */
	public function canSetAndGetInstallationRoot() {
		$this->assertFalse($this->configuration->hasInstallationTargetFolder());
		$this->configuration->setInstallationTargetFolder('foo');
		$this->assertEquals('foo/', $this->configuration->getInstallationTargetFolder(),'could not retrieve install root folder');
		$this->assertTrue($this->configuration->hasInstallationTargetFolder());
	}


	/**
	 * @test
	 */
	public function addServer() {
		$defaultNodes = $this->configuration->getInstallServers();
		$this->assertEquals($defaultNodes, array(), 'Webnodes not empty by default');

		$this->configuration
				->addInstallServer('web1.hostname.com')
				->addInstallServer('web2.hostname.com')
				->addInstallServer('web3.hostname.com');

		$webNodes = $this->configuration->getInstallServers();

		$this->assertEquals($webNodes, array(
			'web1.hostname.com','web2.hostname.com','web3.hostname.com'
		),'Unable to add web nodes');
	}

	/**
	 * @test
	 * @expectedException EasyDeployWorkflows\Exception\InvalidConfigurationException
	 */
	public function canValidateInvalid() {
		$this->configuration->validate();
	}

	/**
	 * @test
	 */
	public function canValidateValid() {
		$this->configuration->setInstallationTargetFolder('foo');
		$this->configuration->addInstallServer('web1.hostname.com');
		$this->configuration->setSource(new EasyDeployWorkflows\Source\File\DownloadSource('http://www.jenkins.my/artifacts/my.tar.gz'));
		$this->configuration->validate();
	}


}