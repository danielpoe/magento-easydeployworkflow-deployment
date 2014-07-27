<?php

namespace EasyDeployWorkflows\Tasks\Common;

use EasyDeployWorkflows\Exception\InvalidConfigurationException;
use EasyDeployWorkflows\Tasks;


class DeleteFolder extends Tasks\AbstractServerTask {

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
			throw new \Exception('Folder "' . $this->folder . '" on Node "' . $server->getHostname() . '" is not present!');
		}
		$this->executeAndLog($server, 'rm -rf ' . $this->folder);
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
