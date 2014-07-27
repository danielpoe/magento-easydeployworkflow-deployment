<?php

namespace EasyDeployWorkflows\Tasks\Common;

use EasyDeployWorkflows\Exception\InvalidConfigurationException;
use EasyDeployWorkflows\Logger\Logger;
use EasyDeployWorkflows\Tasks;


class SetAttributes extends Tasks\Common\RunCommand {

	/**
	 * @var array
	 */
	protected $target = array();

	/**
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * Set target attributes
	 *
	 * @param string $target file, folder, unix file pattern
	 * @param int $permissions
	 * @param string $owner
	 * @param string $group
	 * @param bool $recursive
	 * @return $this
	 * @throws \InvalidArgumentException
	 */
	public function setAttributes($target, $permissions = null, $owner = null, $group = null, $recursive = false) {
		$this->target     = $target;
		$this->attributes = array(
			'permissions' => $permissions,
			'owner'       => $owner,
			'group'       => $group,
			'recursive'   => $recursive,
		);

		return $this;
	}

	/**
	 * Run command on server
	 *
	 * @param Tasks\TaskRunInformation $taskRunInformation
	 * @param \EasyDeploy_AbstractServer $server
	 * @return mixed
	 * @throws \Exception
	 */
	protected function runOnServer(Tasks\TaskRunInformation $taskRunInformation, \EasyDeploy_AbstractServer $server) {
		$commandWorkingDirectory = $server->getCwd();
		if ($this->getChangeToDirectory()) {
			$commandWorkingDirectory = $this->getChangeToDirectory();
		}

		$message = sprintf('Changing attributes of target %s in directory %s', $this->target, $commandWorkingDirectory);
		$this->logger->log($message, Logger::MESSAGE_TYPE_INFO);

		if ($this->attributes['owner'] || $this->attributes['group']) {
			$command = sprintf("chown %s %s:%s '%s'", $this->attributes['recursive'] ? '-R' : '',
				$this->attributes['owner'], $this->attributes['group'], $this->target
			);
			$this->executeAndLog($server, $this->_prependWithCd($command, $taskRunInformation));
		}

		if ($this->attributes['permissions']) {
			$command = sprintf("chmod %s %o '%s'", $this->attributes['recursive'] ? '-R' : '',
				$this->attributes['permissions'], $this->target
			);
			$this->executeAndLog($server, $this->_prependWithCd($command, $taskRunInformation));
		}
	}

	/**
	 * Validate task before running
	 *
	 * @throws InvalidConfigurationException
	 * @return boolean
	 */
	public function validate() {
		if (!isset($this->target)) {
			throw new InvalidConfigurationException('No target is set');
		}

		if ($this->attributes['permissions'] !== null) {
			if (!is_int($this->attributes['permissions']) || $this->attributes['permissions'] > 0777) {
				throw new InvalidConfigurationException('Target permissions must be set as integer value <= 0777');
			}
		}

		return true;
	}
}
