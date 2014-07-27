<?php

namespace EasyDeployWorkflows\Tasks\Common;

use EasyDeployWorkflows\Exception\InvalidConfigurationException;
use EasyDeployWorkflows\Logger\Logger;
use EasyDeployWorkflows\Tasks;

/**
 * Class Untar
 * This Task can Unpackage (Tar or Zip) a package
 *
 * It needs:
 * 	Absolute Path to Package
 *  Folder (where to extract that content) (setFolder)
 *
 * You can also set the path the the expected folder that exists after unpacking the archive
 * - depending on the mode the Task will then skip the extraction if the folder already exists...
 *
 * @package EasyDeployWorkflows\Tasks\Common
 */
class Untar extends Tasks\AbstractServerTask {

	const MODE_SKIP_IF_EXTRACTEDFOLDER_EXISTS   = 1;
	const MODE_DELETE_IF_EXTRACTEDFOLDER_EXISTS = 2;

	protected $folder;

	protected $packagePath;

	protected $expectedExtractedFolder;

	protected $mode;

	public function setExpectedExtractedFolder($expectedExtractedFolder) {
		$this->expectedExtractedFolder = $expectedExtractedFolder;

		return $this;
	}

	public function setFolder($folder) {
		$this->folder = $folder;

		return $this;
	}

	public function setMode($mode) {
		$this->mode = $mode;

		return $this;
	}

	public function setPackagePath($packagePath) {
		$this->packagePath = $packagePath;

		return $this;
	}

	/**
	 * Will init the task to unpack at place
	 *
	 * @param string $path
	 */
	public function autoInitByPackagePath($path) {
		if (empty($path) || $path == '/') {
			throw new \Exception('No path given - cannot autoinit Untar Task');
		}
		$info = pathinfo($path);
		$this->setFolder($info['dirname']);
		//fix .tar.gz
		$extractedFolder = str_replace('.tar', '', $info['filename']);
		$extractedFolder = str_replace('.gz', '', $extractedFolder);
		$extractedFolder = str_replace('.zip', '', $extractedFolder);
		$this->setExpectedExtractedFolder($extractedFolder);
		$this->setChangeToDirectory($info['dirname']);
		$this->setPackagePath($path);

		return $this;
	}

	/**
	 * @param Tasks\TaskRunInformation $taskRunInformation
	 * @param \EasyDeploy_AbstractServer $server
	 * @return mixed
	 * @throws \Exception
	 */
	protected function runOnServer(Tasks\TaskRunInformation $taskRunInformation, \EasyDeploy_AbstractServer $server) {

		$packagePath         = $this->replaceConfigurationMarkersWithTaskRunInformation($this->packagePath, $taskRunInformation);
		$expectedExtractedFolder = $this->replaceConfigurationMarkersWithTaskRunInformation($this->expectedExtractedFolder, $taskRunInformation);
		$changeToDirectory = $this->replaceConfigurationMarkersWithTaskRunInformation($this->changeToDirectory, $taskRunInformation);

		$targetDirectory = rtrim($this->replaceConfigurationMarkersWithTaskRunInformation($this->folder, $taskRunInformation), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;


		if (!empty($expectedExtractedFolder) && $server->isDir($targetDirectory . $expectedExtractedFolder)) {
			if ($this->mode == self::MODE_SKIP_IF_EXTRACTEDFOLDER_EXISTS) {
				$this->logger->log('Extracted folder "' . $targetDirectory . $expectedExtractedFolder . '" already exists! I am skipping the extraction.', Logger::MESSAGE_TYPE_WARNING);

				return;
			} else {
				$this->executeAndLog($server, 'rm -rf ' . $targetDirectory . $expectedExtractedFolder);
			}
		}
		if (!$server->isFile($packagePath)) {
			throw new \Exception("The given file '" . $packagePath . "' doesn't exist.");
		}
		//extract
		if(strpos($packagePath, '.zip') !== false) {
			$this->executeAndLog($server, 'cd ' . $changeToDirectory . '; unzip -d '.$targetDirectory.' -o ' . $packagePath);
		}
		else {
			$args = 'x';
			if (strpos($packagePath, '.gz') !== false) {
				$args = 'xz';
			}
			$this->executeAndLog($server, 'cd ' . $changeToDirectory . '; tar --directory='.$targetDirectory.' -' . $args . 'f ' . $packagePath);
		}
	}

	/**
	 * @return bool
	 * @throws InvalidConfigurationException
	 */
	public function validate() {
		if (empty($this->folder)) {
			throw new InvalidConfigurationException('source not set');
		}
		if (empty($this->packagePath)) {
			throw new InvalidConfigurationException('packagePath not set');
		}
		if (isset($this->expectedExtractedFolder) && substr($this->expectedExtractedFolder,0,1) == '/') {
			throw new InvalidConfigurationException('expectedExtractedFolder set to absolute path. It should be relative to folder.');
		}

		return true;
	}
}
