<?php

namespace EasyDeployWorkflows\Tests\Acceptance\Workflows;

use EasyDeployWorkflows\Workflows as Workflows;

require_once EASYDEPLOY_WORKFLOW_ROOT . 'Classes/Autoloader.php';
require_once EASYDEPLOY_WORKFLOW_ROOT . 'Tests/Mocked/AbstractMockedTest.php';


class MagentoApplicationWorkflowTest extends \EasyDeployWorkflows\Tests\Acceptance\AbstractAcceptanceTest {



	/**
	 * @test
	 */
	public function canDeployApplication() {
		$localServer = new \EasyDeploy_LocalServer();

		$workflowConfiguration = new \EasyDeployWorkflows\Workflows\Application\MagentoApplicationConfiguration();
		$workflowConfiguration->setReleaseBaseFolder($this->targetFolder);
		$workflowConfiguration->setSource(new \EasyDeployWorkflows\Source\Folder\LocalFolderSource(EASYDEPLOY_WORKFLOW_ROOT.'Tests/Acceptance/Fixtures/Source/MagentoApplication'));
		$workflowConfiguration->addInstallServer('localhost');

		$instanceConfiguration = $this->getInitialisedInstanceConfiguration($localServer->getHostname());
		$workflowConfiguration->setReleaseVersion('release1');
		$workflow = new \EasyDeployWorkflows\Workflows\Application\MagentoApplicationWorkflow($instanceConfiguration,$workflowConfiguration);
		$workflow->deploy();

		$this->assertTrue(is_file($this->targetFolder.'/release1/htdocs/index.php'),'Expected to have the /release1/htdocs/index.php target folder:'.$this->targetFolder);
		$this->assertTrue(is_file($this->targetFolder.'/current/htdocs/version.txt'),'Expected to have the version file:'.$this->targetFolder);

	}




}