<?php

namespace EasyDeployWorkflows\Tasks\Backup;

use EasyDeployWorkflows\Tasks;


/**
 * Task that takes care of checking that a backup storage folder exists
 * A backup folder might be required for some installations
 * This task is also able to download the backup if required
 */
class DownloadMinifiedBackup extends \EasyDeployWorkflows\Tasks\AbstractServerTask  {

	/**
	 * @var \EasyDeployWorkflows\Source\File\FileSourceInterface
	 */
	protected $downloadSource;
	protected $backupTargetParentFolder;
	protected $targetFolderName;
	protected $timestampFile;
	protected $timestampFormat='%Y-%m-%d %H:%M:%S';
	/**
	 * @var boolean
	 */
	protected $skipIfTargetPathPresent = false;

	/**
	 * @param $targetFolderName
	 * @return self
	 */
	public function setTargetFolderName($targetFolderName) {
		$this->targetFolderName = $targetFolderName;
		return $this;
	}

	public function getTargetFolderName() {
		return $this->targetFolderName;
	}

	/**
	 * @param boolean $skipIfTargetPathPresent
	 * @return self
	 */
	public function setSkipIfTargetPathPresent($skipIfTargetPathPresent) {
		$this->skipIfTargetPathPresent = $skipIfTargetPathPresent;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getSkipIfTargetPathPresent() {
		return $this->skipIfTargetPathPresent;
	}



	/**
	 * @param $backupTargetParentFolder
	 * @return self
	 */
	public function setBackupTargetParentFolder($backupTargetParentFolder) {
		$this->backupTargetParentFolder = rtrim($backupTargetParentFolder,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
		return $this;
	}

	public function getBackupTargetParentFolder() {
		return $this->backupTargetParentFolder;
	}

	/**
	 * @param \EasyDeployWorkflows\Source\File\FileSourceInterface $downloadSource
	 * @return self
	 */
	public function setDownloadSource(\EasyDeployWorkflows\Source\File\FileSourceInterface $downloadSource) {
		$this->downloadSource = $downloadSource;
		return $this;
	}

	/**
	 * @return \EasyDeployWorkflows\Source\File\FileSourceInterface
	 */
	public function getDownloadSource() {
		return $this->downloadSource;
	}

	/**
	 * @param $timestampFile
	 * @return self
	 */
	public function setTimestampFile($timestampFile) {
		$this->timestampFile = $timestampFile;
		return $this;
	}

	public function getTimestampFile() {
		return $this->timestampFile;
	}

	public function setTimestampFormat($timestampFormat) {
		$this->timestampFormat = $timestampFormat;
	}

	public function getTimestampFormat() {
		return $this->timestampFormat;
	}

	/**
	 * @param TaskRunInformation $taskRunInformation
	 * @return mixed
	 */
	protected function runOnServer(\EasyDeployWorkflows\Tasks\TaskRunInformation $taskRunInformation,\EasyDeploy_AbstractServer $server) {
		if ($this->skipIfTargetPathPresent && $server->isDir($this->getBackupTargetParentFolder() . $this->getTargetFolderName())) {
			$this->logger->log('Skipping because target exists already in: '.$this->getBackupTargetParentFolder() . $this->getTargetFolderName());
			$this->fakeBackupTime($server);
			return;
		}
		if (!$server->isDir($this->getBackupTargetParentFolder())) {
			throw new \Exception('Backup parent folder: ' . $this->getBackupTargetParentFolder() . ' is not existend on server!');
		}
		$task = new \EasyDeployWorkflows\Tasks\Common\SourceEvaluator();
		$task->setSource($this->getDownloadSource());
		$task->setParentFolder($this->getBackupTargetParentFolder());
		$task->setNotIfPathExists($this->getBackupTargetParentFolder() . $this->getDownloadSource()->getFileName());
		$task->addServer($server);
		$task->run($taskRunInformation);

		$task = new \EasyDeployWorkflows\Tasks\Common\Untar();
		$task->autoInitByPackagePath($this->getBackupTargetParentFolder() . $this->getDownloadSource()->getFileName());
		$task->setMode(\EasyDeployWorkflows\Tasks\Common\Untar::MODE_SKIP_IF_EXTRACTEDFOLDER_EXISTS);
		$task->addServer($server);
		$task->setChangeToDirectory($this->getBackupTargetParentFolder());
		$task->run($taskRunInformation);


		if (!$server->exists($this->getBackupTargetParentFolder() . $this->getTargetFolderName())) {
			$task = new \EasyDeployWorkflows\Tasks\Common\Rename();
			$task->addServer($server);
			$task->setSource($this->getBackupTargetParentFolder() . $this->getDownloadSource()->getFileNameWithOutExtension());
			$task->setTarget($this->getBackupTargetParentFolder() . $this->getTargetFolderName());
			$task->run($taskRunInformation);
		}

		$this->fakeBackupTime($server);
	}

	public function validate() {
		if (!isset($this->backupTargetParentFolder)) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('Backup Folder not set');
		}
	}

	/**
	 * @param \EasyDeploy_AbstractServer $server
	 */
	protected function fakeBackupTime(\EasyDeploy_AbstractServer $server) {
		if (isset($this->timestampFile)) {
			$command = 'date +\'' . $this->timestampFormat . '\' > "' . $this->getBackupTargetParentFolder() . $this->getTargetFolderName() . '/' . $this->timestampFile . '"';
			$this->executeAndLog($server, $command);
		}
	}
}
