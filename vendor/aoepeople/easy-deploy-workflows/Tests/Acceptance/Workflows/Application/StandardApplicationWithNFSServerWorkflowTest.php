<?php

namespace EasyDeployWorkflows\Tests\Acceptance\Workflows;

use EasyDeployWorkflows\Workflows as Workflows;

require_once EASYDEPLOY_WORKFLOW_ROOT . 'Classes/Autoloader.php';
require_once EASYDEPLOY_WORKFLOW_ROOT . 'Tests/Mocked/AbstractMockedTest.php';


class StandardApplicationWithNFSServerWorkflowTest extends \EasyDeployWorkflows\Tests\Acceptance\AbstractAcceptanceTest {



	/**
	 * @test
	 */
	public function canDeployBasicApplicationToTargetFolder() {
		$localServer = new \EasyDeploy_LocalServer();

		$workflowConfiguration = new \EasyDeployWorkflows\Workflows\Application\StandardApplicationWithNFSServerConfiguration();
		$workflowConfiguration->setInstallationTargetFolder($this->targetFolder);
		$workflowConfiguration->setSource(new \EasyDeployWorkflows\Source\File\LocalFileSource(EASYDEPLOY_WORKFLOW_ROOT.'Tests/Acceptance/Fixtures/Source/BasicApplication.tar.gz'));
		$workflowConfiguration->setNFSServer('localhost');
		$workflowConfiguration->setDeliveryFolder($this->deliveryFolder);

		$instanceConfiguration = $this->getInitialisedInstanceConfiguration($localServer->getHostname());

		$workflow = new \EasyDeployWorkflows\Workflows\Application\StandardApplicationWithNFSServerWorkflow($instanceConfiguration,$workflowConfiguration);
		$workflow->deploy();

		$this->assertTrue(is_file($this->targetFolder.'/version.txt'),'Expected to have the version.txt file from archive in the target folder:'.$this->targetFolder.'/version.txt');

	}




}