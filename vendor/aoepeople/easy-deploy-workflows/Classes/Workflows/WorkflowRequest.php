<?php

namespace EasyDeployWorkflows\Workflows;


class WorkflowRequest {

	/**
	 * @var string
	 */
	protected $projectName;

	/**
	 * @var string
	 */
	protected $environmentName;

	/**
	 * @var string
	 */
	protected $releaseVersion;

	/**
	 * @var string
	 */
	protected $workFlowConfigurationVariableName;

	/**
	 * @var string
	 */
	protected $instanceConfigurationVariableName = 'instanceConfiguration';

	/**
	 * Defaults to Environmentname
	 * @var string
	 */
	protected $configurationKey;

	/**
	 * @param string $configurationKey
	 */
	public function setConfigurationKey($configurationKey) {
		$this->configurationKey = $configurationKey;
	}

	/**
	 * @return string
	 */
	public function getConfigurationKey() {
		if (!isset($this->configurationKey)) {
			return $this->environmentName;
		}
		return $this->configurationKey;
	}

	/**
	 * @param string $environmentName
	 */
	public function setEnvironmentName($environmentName) {
		$this->environmentName = $environmentName;
	}

	/**
	 * @return string
	 */
	public function getEnvironmentName() {
		return $this->environmentName;
	}

	/**
	 * @param string $instanceConfigurationVariableName
	 */
	public function setInstanceConfigurationVariableName($instanceConfigurationVariableName) {
		$this->instanceConfigurationVariableName = $instanceConfigurationVariableName;
	}

	/**
	 * @return string
	 */
	public function getInstanceConfigurationVariableName() {
		return $this->instanceConfigurationVariableName;
	}

	/**
	 * @param string $projectName
	 */
	public function setProjectName($projectName) {
		$this->projectName = $projectName;
	}

	/**
	 * @return string
	 */
	public function getProjectName() {
		return $this->projectName;
	}

	/**
	 * @param string $releaseVersion
	 */
	public function setReleaseVersion($releaseVersion) {
		$this->releaseVersion = $releaseVersion;
	}

	/**
	 * @return string
	 */
	public function getReleaseVersion() {
		return $this->releaseVersion;
	}

	/**
	 * @param string $workFlowConfigurationVariableName
	 */
	public function setWorkFlowConfigurationVariableName($workFlowConfigurationVariableName) {
		$this->workFlowConfigurationVariableName = $workFlowConfigurationVariableName;
	}

	/**
	 * @return string
	 */
	public function getWorkFlowConfigurationVariableName() {
		return $this->workFlowConfigurationVariableName;
	}


}
