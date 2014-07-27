<?php

namespace EasyDeployWorkflows\Tasks\Common;

use EasyDeployWorkflows\Tasks;

/**
 * copies a file that exists local to a new location which can be remote
 * (new location can be remote (rsync) or local (normal cp)
 */
class CopyLocalFile extends \EasyDeployWorkflows\Tasks\AbstractServerTask  {

	/**
	 * @var string
	 */
	protected $from;

	/**
	 * @var string
	 */
	protected $to;

	/**
	 * @var bool
	 */
	protected $deleteBeforeDownload = false;

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
	 * @param string $from
	 */
	public function setFrom($from) {
		$this->from = $from;
	}

	/**
	 * @return string
	 */
	public function getFrom() {
		return $this->from;
	}

	/**
	 * @param string $to
	 */
	public function setTo($to) {
		$this->to = $to;
	}

	/**
	 * @return string
	 */
	public function getTo() {
		return $this->to;
	}


	/**
	 * @param TaskRunInformation $taskRunInformation
	 * @return mixed
	 */
	protected function runOnServer(\EasyDeployWorkflows\Tasks\TaskRunInformation $taskRunInformation,\EasyDeploy_AbstractServer $server) {

		$from = rtrim($this->replaceConfigurationMarkers($this->from,$taskRunInformation->getWorkflowConfiguration(),$taskRunInformation->getInstanceConfiguration()),DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
		$to = rtrim($this->replaceConfigurationMarkers($this->to,$taskRunInformation->getWorkflowConfiguration(),$taskRunInformation->getInstanceConfiguration()),DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;


		if (!empty($this->notIfPathExists) && ( $server->isFile($this->notIfPathExists) || $server->isDir($this->notIfPathExists) )) {
			$this->logger->log('Skipping because Skip Path is present: "'.$this->notIfPathExists.'"',\EasyDeployWorkflows\Logger\Logger::MESSAGE_TYPE_WARNING);
			return;
		}
		if (isset($GLOBALS['tryRun'])) {
			$this->logger->log('Dry run - will call ->copyLocalFile');
			return;
		}
		$server->copyLocalFile($from,$to);
	}

	/**
	 * @return boolean
	 * throws Exception\InvalidConfigurationException
	 */
	public function validate() {
		if (!isset($this->from)) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('from not set');
		}
		if (!isset($this->to)) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('to not set');
		}
		return true;
	}
}