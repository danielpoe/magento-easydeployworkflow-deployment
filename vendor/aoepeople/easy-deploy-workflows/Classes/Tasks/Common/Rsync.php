<?php

namespace EasyDeployWorkflows\Tasks\Common;

use EasyDeployWorkflows\Tasks;



class Rsync extends \EasyDeployWorkflows\Tasks\AbstractServerTask  {

	/**
	 * @var string
	 */
	protected $sourceFolder;

	/**
	 * @var string
	 */
	protected $targetFolder;


	/**
	 * @param string $sourceFolder
	 * @return self
	 */
	public function setSourceFolder($sourceFolder) {
		$this->sourceFolder = $sourceFolder;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSourceFolder() {
		return $this->sourceFolder;
	}

	/**
	 * @param string $targetFolder
	 * @return self
	 */
	public function setTargetFolder($targetFolder) {
		$this->targetFolder = $targetFolder;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTargetFolder() {
		return $this->targetFolder;
	}



	/**
	 * @param TaskRunInformation $taskRunInformation
	 * @return mixed
	 */
	protected function runOnServer(\EasyDeployWorkflows\Tasks\TaskRunInformation $taskRunInformation,\EasyDeploy_AbstractServer $server) {


		$targetFolder = rtrim($this->replaceConfigurationMarkersWithTaskRunInformation($this->targetFolder,$taskRunInformation),DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
		$sourceFolder = rtrim($this->replaceConfigurationMarkersWithTaskRunInformation($this->sourceFolder,$taskRunInformation),DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

		if (!$server->isDir($sourceFolder)) {
			throw new \Exception('Source Folder "'.$sourceFolder.'" not existend.');
		}

		$this->executeAndLog($server,'rsync -az '.$sourceFolder.' '.$targetFolder);
	}

	/**
	 * @return boolean
	 * throws Exception\InvalidConfigurationException
	 */
	public function validate() {

		if (empty($this->targetFolder)) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('Target Folder not set');
		}

		if (empty($this->sourceFolder)) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('Source Folder not set');
		}

		return true;
	}
}