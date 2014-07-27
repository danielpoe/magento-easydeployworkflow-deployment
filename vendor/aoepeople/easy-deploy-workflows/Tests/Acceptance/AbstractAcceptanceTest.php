<?php

namespace EasyDeployWorkflows\Tests\Acceptance;

abstract class AbstractAcceptanceTest extends \PHPUnit_Framework_TestCase {

	protected $deliveryFolder;
	protected $targetFolder;
	protected $logFolder;

	public function setUp() {
		$this->requireEasyDeployClassesOrSkip();
		$this->cleanAndPrepareTmpFolder();


		$this->assertDirectoryEmpty($this->deliveryFolder);
		$this->assertDirectoryEmpty($this->targetFolder);
	}

	protected function cleanAndPrepareTmpFolder() {
		$this->logFolder = EASYDEPLOY_WORKFLOW_ROOT.'Tests/Acceptance/Tmp/Logs';
		$this->deliveryFolder = EASYDEPLOY_WORKFLOW_ROOT.'Tests/Acceptance/Tmp/Delivery';
		$this->targetFolder = EASYDEPLOY_WORKFLOW_ROOT.'Tests/Acceptance/Tmp/Target';

		exec('rm -rf '.EASYDEPLOY_WORKFLOW_ROOT.'Tests/Acceptance/Tmp');

		mkdir(EASYDEPLOY_WORKFLOW_ROOT.'Tests/Acceptance/Tmp');
		mkdir($this->deliveryFolder);
		mkdir($this->targetFolder);
		mkdir($this->logFolder);
	}

	protected function requireEasyDeployClassesOrSkip() {
		if (is_file(EASYDEPLOY_WORKFLOW_ROOT.'../EasyDeploy/Classes/RemoteServer.php')) {
			require_once EASYDEPLOY_WORKFLOW_ROOT.'../EasyDeploy/Classes/Utils.php';
			\EasyDeploy_Utils::includeAll();
		}

		if (!class_exists('EasyDeploy_RemoteServer')) {
			$this->markTestSkipped(
				'EasyDeploy classes not available.'
			);
		}
	}

	protected function assertDirectoryEmpty($dir) {
		if (is_dir($dir)) {
			$dir = dir($dir);
			while ($file = $dir->read()) {
				if ($file != '.' && $file != '..') {
					$this->fail('Dir not empty: "'.$file.'"');
				}
			}
		}
		else {
			$this->fail('Dir not existend:'.$dir);
		}
		$this->assertTrue(true,'all ok dir is empty');
	}

	public function getInitialisedInstanceConfiguration($hostname) {
		$instanceConfiguration = new \EasyDeployWorkflows\Workflows\InstanceConfiguration();
		$instanceConfiguration->setProjectName('project');
		$instanceConfiguration->setEnvironmentName('production');
		$instanceConfiguration->addAllowedDeployServer($hostname);
		\EasyDeployWorkflows\Logger\Logger::getInstance()->setLogFile($this->logFolder . '/deploy.log');

		$instanceConfiguration->setDeployLogFolder($this->logFolder);
		return $instanceConfiguration;
	}

	public function getInitializedTaskRunInformation($localServer) {
		$instanceConfiguration = $this->getInitialisedInstanceConfiguration($localServer->getHostname());
		$workflowConfiguration = $this->getMock('\EasyDeployWorkflows\Workflows\AbstractWorkflowConfiguration');
		$taskRunInformation = new \EasyDeployWorkflows\Tasks\TaskRunInformation();
		$taskRunInformation->setInstanceConfiguration($instanceConfiguration);
		$taskRunInformation->setWorkflowConfiguration($workflowConfiguration);
		return $taskRunInformation;
	}
}

?>