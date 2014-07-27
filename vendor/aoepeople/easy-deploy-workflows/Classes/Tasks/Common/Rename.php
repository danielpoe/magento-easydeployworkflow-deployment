<?php

namespace EasyDeployWorkflows\Tasks\Common;

use EasyDeployWorkflows\Exception\InvalidConfigurationException;
use EasyDeployWorkflows\Tasks;


class Rename extends Tasks\AbstractServerTask {

	const MODE_SKIP_IF_TARGET_EXISTS   = 1;
	const MODE_FAIL_IF_TARGET_EXISTS = 2;

	/**
	 * @var string
	 */
	protected $source;

	/**
	 * @var string
	 */
	protected $target;

	/**
	 * @var int
	 */
	protected $mode = 2;

	/**
	 * @param string $source
	 * @return $this
	 */
	public function setSource($source)
	{
		$this->source = $source;

		return $this;
	}

	public function setMode($mode) {
		$this->mode = $mode;

		return $this;
	}

	/**
	 * @param string $target
	 * @return $this
	 */
	public function setTarget($target)
	{
		$this->target = $target;

		return $this;
	}

	/**
	 * @param Tasks\TaskRunInformation $taskRunInformation
	 * @param \EasyDeploy_AbstractServer $server
	 * @return mixed
	 */
	protected function runOnServer(Tasks\TaskRunInformation $taskRunInformation,\EasyDeploy_AbstractServer $server) {
		$source = $this->replaceConfigurationMarkersWithTaskRunInformation($this->source,$taskRunInformation);
		$target = $this->replaceConfigurationMarkersWithTaskRunInformation($this->target,$taskRunInformation);
		if ($server->isDir($target) || $server->isFile($target)) {
			if ($this->mode == self::MODE_FAIL_IF_TARGET_EXISTS) {
				throw new \Exception('Target "'.$target.'" for the Rename Task already existend!');
			}
			else if ($this->mode == self::MODE_SKIP_IF_TARGET_EXISTS) {
				$this->logger->log('Target "'.$target.'" for the Rename Task already existend! Skipping the Rename',LOG_WARNING);
				return;
			}
		}
		$this->executeAndLog($server,'mv '.$source.' '.$target);
	}

	/**
	 * @return boolean
	 * @throws InvalidConfigurationException
	 */
	public function validate() {
		if (empty($this->source)) {
			throw new InvalidConfigurationException('source not set');
		}
		if (empty($this->target)) {
			throw new InvalidConfigurationException('target not set');
		}
	}
}
