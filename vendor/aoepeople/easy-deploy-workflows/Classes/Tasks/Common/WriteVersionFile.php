<?php

namespace EasyDeployWorkflows\Tasks\Common;

use EasyDeployWorkflows\Tasks;



class WriteVersionFile extends \EasyDeployWorkflows\Tasks\AbstractServerTask  {

	/**
	 * @var string
	 */
	protected $version;

	/**
	 * @var string
	 */
	protected $targetPath;

	/**
	 * @param string $version
	 */
	public function setVersion($version) {
		$this->version = $version;
	}

	/**
	 * @return string
	 */
	public function getVersion() {
		return $this->version;
	}

	/**
	 * @param string $targetPath
	 */
	public function setTargetPath($targetPath) {
		$this->targetPath = rtrim($targetPath,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
	}

	/**
	 * @return string
	 */
	public function getTargetPath() {
		return $this->targetPath;
	}

	/**
	 * @param TaskRunInformation $taskRunInformation
	 * @return mixed
	 */
	protected function runOnServer(\EasyDeployWorkflows\Tasks\TaskRunInformation $taskRunInformation,\EasyDeploy_AbstractServer $server) {
			$this->executeAndLog($server,'echo "'.$this->version.'" > '.$this->targetPath.'version.txt');
		$this->executeAndLog($server,'echo "'.gmdate('r').'" > '.$this->targetPath.'deploytime.txt');
	}

	/**
	 * @return boolean
	 * throws Exception\InvalidConfigurationException
	 */
	public function validate() {
		if (empty($this->targetPath)) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('targetPath not set');
		}
		if (empty($this->version)) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('version not set');
		}
	}
}