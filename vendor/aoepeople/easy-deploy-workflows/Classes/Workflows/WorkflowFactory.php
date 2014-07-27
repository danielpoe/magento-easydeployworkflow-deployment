<?php

namespace EasyDeployWorkflows\Workflows;

use EasyDeployWorkflows\Logger\Logger;
use EasyDeployWorkflows\Workflows;
use EasyDeployWorkflows\Workflows\Exception\WorkflowConfigurationNotExistendException;

class WorkflowFactory {

	/**
	 * @var string
	 */
	protected $configurationFolder;

	/**
	 * Set the folder by convention
	 */
	public function __construct() {
		$this->autoSetConfigurationFolder();
	}

	/**
	 * @param string $configurationFolder
	 */
	public function setConfigurationFolder($configurationFolder) {
		$this->configurationFolder = $configurationFolder;
	}

	/**
	 * Creates the workflow depending on the passed configuration.
	 *
	 * @param InstanceConfiguration $instanceConfiguration
	 * @param AbstractWorkflowConfiguration $workflowConfiguration
	 * @throws WorkflowConfigurationNotExistendException
	 * @return AbstractWorkflow
	 */
	public function create(InstanceConfiguration $instanceConfiguration,
		AbstractWorkflowConfiguration $workflowConfiguration
	) {
		if (!class_exists($workflowConfiguration->getWorkflowClassName())) {
			throw new WorkflowConfigurationNotExistendException('Workflow "'
				. $workflowConfiguration->getWorkflowClassName() . '" doesn\'t exist or not loaded', 2212
			);
		}
		$this->initLoggerLogFile($instanceConfiguration, $workflowConfiguration);
		$workflowClass = $workflowConfiguration->getWorkflowClassName();

		$workflow = $this->getWorkflow($workflowClass, $instanceConfiguration, $workflowConfiguration);
		$workflow->injectDownloader(new \EasyDeploy_Helper_Downloader());

		return $workflow;
	}

	/**
	 * @param InstanceConfiguration $instanceConfiguration
	 * @param AbstractWorkflowConfiguration $workflowConfiguration
	 */
	protected function initLoggerLogFile(InstanceConfiguration $instanceConfiguration,
		AbstractWorkflowConfiguration $workflowConfiguration
	) {
		$currentLogFile = Logger::getInstance()->getLogFile();
		if (!empty($currentLogFile)) {
			return;
		}

		if ($instanceConfiguration->hasValidDeployLogFolder()) {
			$logDir = $instanceConfiguration->getDeployLogFolder();
		} else {
			if (!empty($_SERVER['SCRIPT_NAME'])) {
				if (substr($_SERVER['SCRIPT_NAME'], 0, 1) === '/') {
					$deployScript = $_SERVER['SCRIPT_NAME'];
				} else {
					$deployScript = $_SERVER['PWD'] . '/' . $_SERVER['SCRIPT_NAME'];
				}
				$logDir = dirname($deployScript);
			} else {
				$logDir = dirname(__FILE__) . '/../../../';
			}
		}
		Logger::getInstance()->setLogFile(
			rtrim($logDir, '/') . '/deploy-' . $workflowConfiguration->getReleaseVersion() . '-' . date('d.m.Y') . '.log'
		);
	}

	/**
	 * @DEPRICATED
	 * @param string $projectName
	 * @param string $environmentName
	 * @param string $releaseVersion
	 * @param string $workFlowConfigurationVariableName
	 * @param string $instanceConfigurationVariableName
	 * @return AbstractWorkflow
	 * @throws \Exception
	 */
	public function createByConfigurationVariable($projectName, $environmentName, $releaseVersion,
		$workFlowConfigurationVariableName, $instanceConfigurationVariableName = 'instanceConfiguration'
	) {
		$request = new WorkflowRequest();
		$request->setProjectName($projectName);
		$request->setEnvironmentName($environmentName);
		$request->setReleaseVersion($releaseVersion);
		$request->setWorkFlowConfigurationVariableName($workFlowConfigurationVariableName);
		$request->setInstanceConfigurationVariableName($instanceConfigurationVariableName);
		return $this->createByRequest($request);
	}

	public function createByRequest(WorkflowRequest $request) {
		if (!is_dir($this->configurationFolder)) {
			throw new \Exception('Configuration folder "' . $this->configurationFolder . '" doesn\'t exist. Please check if you followed the convention - or set your configuration folder explicitly');
		}
		$configurationFile = $this->configurationFolder . $request->getProjectName() . DIRECTORY_SEPARATOR . $request->getConfigurationKey() . '.php';
		if (!is_file($configurationFile)) {
			throw new \Exception('No configuration file found for project and environment. Looking in: ' . $configurationFile);
		}
		include($configurationFile);
		$instanceConfigurationVariableName = $request->getInstanceConfigurationVariableName();
		if (!isset($$instanceConfigurationVariableName)) {
			Logger::getInstance()->log('No Instance Configuration found! Expect a variable $' . $instanceConfigurationVariableName . '. I am creating a default one now...');
			$instanceConfiguration = new InstanceConfiguration();
			$instanceConfiguration->addAllowedDeployServer('*')
					->setEnvironmentName($request->getEnvironmentName())
					->setProjectName($request->getProjectName())
					->setTitle('Default Instance Configuration');
		}

		if (!$$instanceConfigurationVariableName instanceof InstanceConfiguration) {
			throw new \Exception('No Instance Configuration found! Expect  $' . $instanceConfigurationVariableName . '  is instance of "InstanceConfiguration".');
		}

		/** @var InstanceConfiguration $instanceConfiguration */
		$instanceConfiguration = $$instanceConfigurationVariableName;
		if ($instanceConfiguration->getEnvironmentName() != $request->getEnvironmentName()
				|| $instanceConfiguration->getProjectName() != $request->getProjectName()
		) {
			throw new \Exception('Instance Environment Data invalid! Check that project and environment is set and valid! Current:' . $instanceConfiguration->getProjectName() . ' / ' . $instanceConfiguration->getEnvironmentName());
		}
		$workFlowConfigurationVariableName = $request->getWorkFlowConfigurationVariableName();
		if (!isset($$workFlowConfigurationVariableName) || !$$workFlowConfigurationVariableName instanceof AbstractWorkflowConfiguration
		) {
			throw new WorkflowConfigurationNotExistendException('No Workflow Configuration found or it is invalid! Expected a Variable with the name $' . $workFlowConfigurationVariableName);
		}
		$$workFlowConfigurationVariableName->setReleaseVersion($request->getReleaseVersion());
		return $this->create($instanceConfiguration, $$workFlowConfigurationVariableName);
	}

	/**
	 * @param $name
	 * @param InstanceConfiguration $instanceConfiguration
	 * @param AbstractWorkflowConfiguration $workflowConfiguration
	 * @return AbstractWorkflow
	 */
	protected function getWorkflow($name, InstanceConfiguration $instanceConfiguration,
		AbstractWorkflowConfiguration $workflowConfiguration
	) {
		return new $name($instanceConfiguration, $workflowConfiguration);
	}

	/**
	 * from _SERVER env
	 */
	private function autoSetConfigurationFolder() {
		$scriptDir = dirname($_SERVER['PWD'] . DIRECTORY_SEPARATOR . $_SERVER['SCRIPT_NAME']);
		if (is_dir($scriptDir . DIRECTORY_SEPARATOR . 'Configuration')) {
			$this->setConfigurationFolder($scriptDir . DIRECTORY_SEPARATOR . 'Configuration' . DIRECTORY_SEPARATOR);
		} else {
			$this->setConfigurationFolder(dirname(__FILE__) . '/../../../Configuration/');
		}
	}
}
