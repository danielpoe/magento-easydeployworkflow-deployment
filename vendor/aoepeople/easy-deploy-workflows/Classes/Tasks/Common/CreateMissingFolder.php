<?php

namespace EasyDeployWorkflows\Tasks\Common;

use EasyDeployWorkflows\Exception\InvalidConfigurationException;
use EasyDeployWorkflows\Logger\Logger;
use EasyDeployWorkflows\Tasks;


class CreateMissingFolder extends Tasks\AbstractServerTask {

	/**
	 * @var string
	 */
	protected $folder;

	/**
	 * @param string $folder
	 */
	public function setFolder($folder) {
		$this->folder = $folder;
	}

	/**
	 * @param Tasks\TaskRunInformation $taskRunInformation
	 * @param \EasyDeploy_AbstractServer $server
	 * @return mixed
	 * @throws \Exception
	 */
	protected function runOnServer(Tasks\TaskRunInformation $taskRunInformation, \EasyDeploy_AbstractServer $server) {
		if (!$server->isDir($this->folder)) {
			$message = 'Expected Folder is not present! Try to create "' . $this->folder . '"';
			$this->logger->log($message);

			$this->executeAndLog($server, 'mkdir -p ' . $this->folder);
			$this->executeAndLog($server, 'chmod g+rws ' . $this->folder);
		}

		if (!$server->isDir($this->folder)) {
			$message = 'Folder  "' . $this->folder . '" is not present! Could not create!';
			$this->logger->log($message, Logger::MESSAGE_TYPE_ERROR);
			throw new \Exception('Folder on Node "' . $server->getHostname() . '" is not present!');
		}
	}

	/**
	 * @return boolean
	 * @throws InvalidConfigurationException
	 */
	public function validate() {
		if (!isset($this->folder)) {
			throw new InvalidConfigurationException('Folder not set');
		}

		return true;
	}
}
