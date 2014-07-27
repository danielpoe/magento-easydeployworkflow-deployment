<?php

namespace EasyDeployWorkflows\Tasks\Common;

use EasyDeployWorkflows\Tasks;



class CopyFile extends \EasyDeployWorkflows\Tasks\AbstractServerTask  {

	/**
	 * @var string
	 */
	protected $sourceFile;

	/**
	 * @var string
	 */
	protected $targetFile;

	/**
	 * @param string $file
	 */
	public function setSourceFile($sourceFile)
	{
		$this->sourceFile = $sourceFile;
	}

	/**
	 * @param string $file
	 */
	public function setTargetFile($targetFile)
	{
		$this->targetFile = $targetFile;
	}

	/**
	 * @param TaskRunInformation $taskRunInformation
	 * @return mixed
	 */
	protected function runOnServer(\EasyDeployWorkflows\Tasks\TaskRunInformation $taskRunInformation,\EasyDeploy_AbstractServer $server) {
			$server->run('cp -f '.$this->sourceFile.' '.$this->targetFile);
	}

	/**
	 * @return boolean
	 * throws Exception\InvalidConfigurationException
	 */
	public function validate() {
		if (empty($this->sourceFile)) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('sourceFile not set');
		}
		if (empty($this->targetFile)) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('targetFile not set');
		}
	}
}