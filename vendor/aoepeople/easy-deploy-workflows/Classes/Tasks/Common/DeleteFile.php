<?php

namespace EasyDeployWorkflows\Tasks\Common;

use EasyDeployWorkflows\Tasks;


class DeleteFile extends Tasks\AbstractServerTask {

	/**
	 * @var string
	 */
	protected $file;

	/**
	 * @param string $file
	 * @return $this
	 */
	public function setFile($file) {
		$this->file = $file;

		return $this;
	}

	/**
	 * @param Tasks\TaskRunInformation $taskRunInformation
	 * @param \EasyDeploy_AbstractServer $server
	 * @return mixed
	 */
	protected function runOnServer(Tasks\TaskRunInformation $taskRunInformation, \EasyDeploy_AbstractServer $server) {
		$server->run('rm ' . $this->file);
	}

	/**
	 * @return boolean
	 */
	public function validate() {
		return true;
	}
}
