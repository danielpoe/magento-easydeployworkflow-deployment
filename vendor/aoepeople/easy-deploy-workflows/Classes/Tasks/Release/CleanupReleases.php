<?php

namespace EasyDeployWorkflows\Tasks\Release;

use EasyDeployWorkflows\Tasks;


/**
 * Task removes old releases in the given releasefolder
 * Inspired by: http://git.typo3.org/FLOW3/Packages/TYPO3.Surf.git/blob/HEAD:/Classes/TYPO3/Surf/Task/CleanupReleasesTask.php
 */
class CleanupReleases extends \EasyDeployWorkflows\Tasks\AbstractServerTask  {

	/**
	 * @var string
	 */
	protected $releasesBaseFolder;

	/**
	 * How many releases (current and previous not counted) should be keept
	 * @var int
	 */
	protected $keepReleases = 4;

	/**
	 * @param int $keepReleases
	 */
	public function setKeepReleases($keepReleases) {
		$this->keepReleases = intval($keepReleases);
	}

	/**
	 * @return int
	 */
	public function getKeepReleases() {
		return $this->keepReleases;
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
		$releaseFolder = rtrim($this->replaceConfigurationMarkersWithTaskRunInformation($this->releasesBaseFolder,$taskRunInformation),DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

		$currentReleaseIdentifier = trim($server->run('if [ -h '.$releaseFolder.'previous ]; then basename `readlink '.$releaseFolder.'previous` ; fi',FALSE,TRUE));
		$previousReleaseIdentifier = trim($server->run('if [ -h '.$releaseFolder.'current ]; then basename `readlink '.$releaseFolder.'current` ; fi',FALSE,TRUE));
		$allReleasesList = $server->run('find '.$releaseFolder.'. -maxdepth 1 -type d -exec basename {} \;',FALSE,TRUE);
		$allReleases = preg_split('/\s+/', $allReleasesList, -1, PREG_SPLIT_NO_EMPTY);
		$removableReleases = array();
		foreach ($allReleases as $release) {
			$release = trim($release);
			if ($release !== '.' && $release !== $currentReleaseIdentifier && $release !== $previousReleaseIdentifier && $release !== 'current' && $release !== 'previous') {
				$removableReleases[] = trim($release);
			}
		}
		sort($removableReleases);
		$this->logger->log('Removeable Releases:'.var_export($removableReleases,true),\EasyDeployWorkflows\Logger\Logger::MESSAGE_TYPE_DEBUG);
		$removeReleases = array_slice($removableReleases, 0, count($removableReleases) - $this->keepReleases);

		foreach ($removeReleases as $removeRelease) {
			$this->logger->log('Removing old release "'.$removeRelease.'" in Folder "'.$releaseFolder.'"');
			$this->executeAndLog($server,'rm -rf '.$releaseFolder.$removeRelease);
		}

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