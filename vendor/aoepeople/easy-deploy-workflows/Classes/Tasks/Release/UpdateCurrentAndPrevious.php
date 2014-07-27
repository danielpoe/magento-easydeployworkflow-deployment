<?php

namespace EasyDeployWorkflows\Tasks\Release;

use EasyDeployWorkflows\Tasks;



class UpdateCurrentAndPrevious extends \EasyDeployWorkflows\Tasks\AbstractServerTask  {

	/**
	 * @var string
	 */
	protected $releasesBaseFolder;

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
			throw new \Exception('Release Folder "'.$releasesBaseFolder.'" not existend.');
		}
		$nextRelease = trim($server->run('cd ' . $releasesBaseFolder . ' && readlink next',FALSE,TRUE));
		if (empty($nextRelease)) {
			throw new \Exception('No next symlink exists!');
		}
		$this->executeAndLog($server, 'cd ' . $releasesBaseFolder . ' && rm -f ./previous && if [ -e ./current ]; then mv ./current ./previous; fi && ln -s '.$nextRelease.' ./current');

	}

	/**
	 * @return boolean
	 * throws Exception\InvalidConfigurationException
	 */
	public function validate() {

		if (empty($this->releasesBaseFolder)) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('releasesBaseFolder Folder not set');
		}

		return true;
	}
}