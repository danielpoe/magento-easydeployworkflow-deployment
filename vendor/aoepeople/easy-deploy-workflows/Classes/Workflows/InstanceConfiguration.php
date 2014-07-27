<?php

namespace EasyDeployWorkflows\Workflows;

use EasyDeployWorkflows\Workflows;
use EasyDeployWorkflows\Workflows\Exception as Exception;

class InstanceConfiguration extends AbstractConfiguration {

	/**
	 * @var string
	 */
	protected $deployLogFolder = '';


	/**
	 * @var string
	 */
	protected $environmentName = '';

	/**
	 * @var string
	 */
	protected $projectName = '';

	/**
	 * @param $hostname
	 * @return InstanceConfiguration
	 */
	public function addAllowedDeployServer($hostname) {
		$this->addServer($hostname,'allowed_deploy_servers');

		return $this;
	}

	/**
	 * @return array
	 */
	public function getAllowedDeployServers() {
		return $this->getServers('allowed_deploy_servers');
	}

	/**
	 * @param $hostname
	 * @return bool
	 */
	public function isAllowedDeployServer($hostname) {
		foreach ($this->getAllowedDeployServers() as $serverName) {
			if ($serverName == $hostname || $serverName == '*') {
				return true;
			}
		}
		return false;
	}

	/**
	 * @return boolean
	 */
	public function hasAllowedDeployServers() {
		return count($this->getAllowedDeployServers()) > 0;
	}

	/**
	 * @param string $environmentName
	 * @return \EasyDeployWorkflows\Workflows\InstanceConfiguration
	 */
	public function setEnvironmentName($environmentName) {
		$this->environmentName = $environmentName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getEnvironmentName() {
		return $this->environmentName;
	}

	/**
	 * @return bool
	 */
	public function hasEnvironmentName() {
		return $this->environmentName != '';
	}

	/**
	 * @param string $projectName
	 * @return \EasyDeployWorkflows\Workflows\InstanceConfiguration
	 */
	public function setProjectName($projectName) {
		$this->projectName = $projectName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getProjectName() {
		return $this->projectName;
	}

	/**
	 * @return bool
	 * @throws Exception\InvalidConfigurationException
	 */
	public function validate() {
		if(!$this->hasAllowedDeployServers()) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('Please configure an allowed deploy server!');
		}

		if(!$this->hasEnvironmentName()) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('Please configure an environment name!');
		}

		return true;
	}

	/**
	 * @param string $deployLogFolder
	 */
	public function setDeployLogFolder($deployLogFolder)
	{
		$this->deployLogFolder = $deployLogFolder;
	}

	/**
	 * @return string
	 */
	public function getDeployLogFolder()
	{
		return $this->deployLogFolder;
	}

	/**
	 * @return string
	 */
	public function hasValidDeployLogFolder()
	{
		return isset($this->deployLogFolder) && is_dir($this->deployLogFolder);
	}
}
