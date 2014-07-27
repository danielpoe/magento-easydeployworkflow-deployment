<?php

namespace EasyDeployWorkflows\Tasks\Common;

use EasyDeployWorkflows\Tasks;


class SourceEvaluator extends \EasyDeployWorkflows\Tasks\AbstractServerTask  {

	/**
	 * @var \EasyDeployWorkflows\Source\SourceInterface
	 */
	protected $source;

	/**
	 * @var bool
	 */
	protected $deleteBeforeDownload = false;

	/**
	 * @var string
	 */
	protected $parentFolder;

	/**
	 * @var string if file is not existend
	 */
	protected $notIfPathExists = '';

	/**
	 * @param string $notIfFileExists
	 */
	public function setNotIfPathExists($notIfPathExists)
	{
		$this->notIfPathExists = $notIfPathExists;
		return $this;
	}



	public function __construct() {
		parent::__construct();
	}


	/**
	 * @param boolean $deleteBeforeDownload
	 */
	public function setDeleteBeforeDownload($deleteBeforeDownload)
	{
		$this->deleteBeforeDownload = $deleteBeforeDownload;
	}

	/**
	 * @return boolean
	 */
	public function getDeleteBeforeDownload()
	{
		return $this->deleteBeforeDownload;
	}

	/**
	 * @return \EasyDeployWorkflows\Source\SourceInterface
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * @param \EasyDeployWorkflows\Source\SourceInterface $source
	 */
	public function setSource(\EasyDeployWorkflows\Source\SourceInterface $source)
	{
		$this->source = $source;
	}

	/**
	 * @param string $parentFolder
	 */
	public function setParentFolder($parentFolder) {
		$this->parentFolder = $parentFolder;
	}

	/**
	 * @return string
	 */
	public function getParentFolder() {
		return $this->parentFolder;
	}


	/**
	 * @param TaskRunInformation $taskRunInformation
	 * @return mixed
	 */
	protected function runOnServer(\EasyDeployWorkflows\Tasks\TaskRunInformation $taskRunInformation,\EasyDeploy_AbstractServer $server) {

		$parentFolder = rtrim($this->replaceConfigurationMarkers($this->parentFolder,$taskRunInformation->getWorkflowConfiguration(),$taskRunInformation->getInstanceConfiguration()),DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

		if (!empty($this->notIfPathExists) && ( $server->isFile($this->notIfPathExists) || $server->isDir($this->notIfPathExists) )) {
			$this->logger->log('Skipping because Skip Path is present: "'.$this->notIfPathExists.'"',\EasyDeployWorkflows\Logger\Logger::MESSAGE_TYPE_WARNING);
			return;
		}

		# skip or delete file if existend
		if ($this->source instanceof \EasyDeployWorkflows\Source\File\FileSourceInterface
			&& $server->isFile($parentFolder.$this->source->getFileName())) {
			if ($this->deleteBeforeDownload) {
				$this->executeAndLog($server,'rm '.$parentFolder.$this->source->getFileName());
			}
			else {
				$this->logger->log('Target File "'.$parentFolder.$this->source->getFileName().'" already exists! I am skipping the download!',\EasyDeployWorkflows\Logger\Logger::MESSAGE_TYPE_WARNING);
				return;
			}
		}
		# skip if folder is existend
		if ($this->source instanceof \EasyDeployWorkflows\Source\Folder\FolderSourceInterface
			&& $server->isDir($parentFolder.$this->source->getFolderName())) {
			$this->logger->log('Target Folder "'.$parentFolder.$this->source->getFolderName().'" already exists! I am skipping the download!',\EasyDeployWorkflows\Logger\Logger::MESSAGE_TYPE_WARNING);
			return;
		}
		# download
		if (!$server->isDir($parentFolder)) {
			$this->executeAndLog($server, 'mkdir '.$parentFolder);
		}
		$this->logger->log('Download Infos: '. $this->replaceConfigurationMarkersWithTaskRunInformation($this->source->getShortExplain(),$taskRunInformation).' to '.$parentFolder.' on server '.$server->getHostname());
		$command = $this->replaceConfigurationMarkersWithTaskRunInformation($this->source->getDownloadCommand($parentFolder),$taskRunInformation);
		$this->executeAndLog($server, $command);
		$this->logger->log('Download ready');
	}

	/**
	 * @return boolean
	 * throws Exception\InvalidConfigurationException
	 */
	public function validate() {
		if (!isset($this->source)) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('source not set');
		}
		if (!isset($this->parentFolder)) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('parentFolder not set');
		}
		return true;
	}
}