<?php

namespace EasyDeployWorkflows\Tasks\Release;

use EasyDeployWorkflows\Tasks;



class UpdateNext extends \EasyDeployWorkflows\Tasks\AbstractServerTask  {

	/**
	 * @var string
	 */
	protected $nextRelease;

	/**
	 * @var string
	 */
	protected $releasesBaseFolder;

	/**
	 * @param string $nextRelease
	 */
	public function setNextRelease($nextRelease) {
		$this->nextRelease = $nextRelease;
	}

	/**
	 * @return string
	 */
	public function getNextRelease() {
		return $this->nextRelease;
	}

	/**
	 * @param string $releaseFolder
	 */
	public function setReleasesBaseFolder($releaseFolder) {
		$this->releasesBaseFolder = $releaseFolder;
	}

	/**
	 * @return string
	 */
	public function getReleasesBaseFolder() {
		return $this->releasesBaseFolder;
	}



	/**
	 * @param TaskRunInformation $taskRunInformation
	 * @return mixed
	 */
	protected function runOnServer(\EasyDeployWorkflows\Tasks\TaskRunInformation $taskRunInformation,\EasyDeploy_AbstractServer $server) {

		$releasesBaseFolder = rtrim($this->replaceConfigurationMarkersWithTaskRunInformation($this->releasesBaseFolder,$taskRunInformation),DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

		if (!$server->isDir($releasesBaseFolder)) {
			throw new \Exception('Release Base Folder "'.$releasesBaseFolder.'" not existend.');
		}

		if (!$server->isDir($releasesBaseFolder.$this->nextRelease)) {
			throw new \Exception('Next Release Folder "'.$releasesBaseFolder.$this->nextRelease.'" not existend on server '.$server->getInternalTitle());
		}
		$this->executeAndLog($server,'cd '.$releasesBaseFolder.'; ln -snf '.$this->nextRelease.' next');
	}

	/**
	 * @return boolean
	 * throws Exception\InvalidConfigurationException
	 */
	public function validate() {

		if (empty($this->releasesBaseFolder)) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('releasesBaseFolder Folder not set');
		}

		if (empty($this->nextRelease)) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('nextRelease not set');
		}

		return true;
	}
}