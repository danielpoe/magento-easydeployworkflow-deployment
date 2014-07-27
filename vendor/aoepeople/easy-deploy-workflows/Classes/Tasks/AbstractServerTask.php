<?php

namespace EasyDeployWorkflows\Tasks;

use EasyDeployWorkflows\Exception\InvalidConfigurationException;
use EasyDeployWorkflows\Logger\Logger;
use EasyDeployWorkflows\Workflows;


/**
 * A task that is executed on one or many servers
 */
abstract class AbstractServerTask extends AbstractTask {

	/**
	 * @var \EasyDeploy_AbstractServer[]
	 */
	protected $servers = array();

	/**
	 * @var string
	 */
	protected $changeToDirectory;

	/**
	 * @var bool
	 */
	protected $runInBackground = false;

	/**
	 * Adds a server on which this task should be executed
	 *
	 * @param \EasyDeploy_AbstractServer $server
	 * @return $this
	 */
	public function addServer(\EasyDeploy_AbstractServer $server) {
		$this->servers[$server->getInternalTitle()] = $server;

		return $this;
	}

	/**
	 * @return \EasyDeploy_AbstractServer[]
	 */
	public function getServers() {
		return $this->servers;
	}

	/**
	 * @return bool
	 */
	public function hasServers() {
		return count($this->servers) > 0;
	}

	/**
	 * Adds a server on which this task should be executed
	 *
	 * @param string $server
	 * @return $this
	 * @throws \InvalidArgumentException
	 */
	public function addServerByName($server) {
		if (!is_string($server)) {
			throw new \InvalidArgumentException('no string given: ' . gettype($server));
		}
		$this->addServer($this->getServer($server));

		return $this;
	}

	/**
	 * Adds servers on which this task should be executed
	 *
	 * @param string[] $servers
	 * @return $this
	 */
	public function addServersByName(array $servers) {
		foreach ($servers as $server) {
			$this->addServerByName($server);
		}

		return $this;
	}

	/**
	 * @param TaskRunInformation $taskRunInformation
	 * @return mixed|void
	 * @throws InvalidConfigurationException
	 */
	public function run(TaskRunInformation $taskRunInformation) {
		$this->validate();
		if (!$this->hasServers()) {
			throw new InvalidConfigurationException('No server set for server based task: ' . get_class($this));
		}
		foreach ($this->getServers() as $server) {
			$this->logger->log('Run on Server ' . $server->getInternalTitle());
			$this->logger->addLogIndentLevel();
			$this->runOnServer($taskRunInformation, $server);
			$this->logger->removeLogIndentLevel();
		}
	}

	/**
	 * Set a directory that should be changed to before extracting the archive.
	 * If not set the directory of the archive is used
	 *
	 * @param string $changeToDirectory
	 * @return $this
	 */
	public function setChangeToDirectory($changeToDirectory) {
		$this->changeToDirectory = rtrim($changeToDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getChangeToDirectory() {
		return $this->changeToDirectory;
	}

	/**
	 * @return boolean
	 */
	public function hasChangeToDirectorySet() {
		return isset($this->changeToDirectory);
	}

	/**
	 * @param boolean $runInBackground
	 * @return $this
	 */
	public function setRunInBackground($runInBackground) {
		$this->runInBackground = $runInBackground;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getRunInBackground() {
		return $this->runInBackground;
	}

	/**
	 * Use this function as Wrapper to $server->run($command) to ensure that the output is logged properly
	 * Also this function takes care of tryRun
	 *
	 * @param \EasyDeploy_AbstractServer $server
	 * @param string $command
	 * @return null
	 */
	protected function executeAndLog(\EasyDeploy_AbstractServer $server, $command) {
		$this->logger->log($command, Logger::MESSAGE_TYPE_COMMAND);
		if (isset($GLOBALS['tryRun'])) {
			return null;
		}
		$this->logger->log('', Logger::MESSAGE_TYPE_COMMANDOUTPUT);

		return $server->run($command, false, false, $this->logger->getLogFile());
	}

	/**
	 * @param TaskRunInformation $taskRunInformation
	 * @param \EasyDeploy_AbstractServer $server
	 * @return mixed
	 */
	abstract protected function runOnServer(TaskRunInformation $taskRunInformation, \EasyDeploy_AbstractServer $server);

	/**
	 * Prepend $command with cd $this->changeToDirectory;
	 *
	 * @param string $command
	 * @param TaskRunInformation $taskRunInformation
	 * @return string
	 */
	protected function _prependWithCd($command, TaskRunInformation $taskRunInformation) {
		if (isset($this->changeToDirectory)) {
			$changeToDirectory = rtrim(
				$this->replaceConfigurationMarkersWithTaskRunInformation($this->changeToDirectory, $taskRunInformation),
				DIRECTORY_SEPARATOR
			) . DIRECTORY_SEPARATOR;
			$command = 'cd ' . $changeToDirectory . '; ' . $command;
		}

		return $command;
	}

	/**
	 * @param $command
	 * @param array $whiteList
	 * @return string
	 */
	protected function _prependWithEnvVarExport($command, $whiteList = array()) {
		foreach ($_ENV as $key => $value) {
			if (!empty($whiteList) && !in_array($key,$whiteList)) {
				continue;
			}
			$command = 'export '.$key.'="'.escapeshellarg($value).'" && '.$command;
		}

		return $command;
	}

	/**
	 * Append to $command >/dev/null & to run it in the background
	 *
	 * @param string $command
	 * @return string
	 */
	protected function _appendRunInBackground($command)
	{
		if ($this->runInBackground) {
			$command .= ' >/dev/null &';
		}

		return $command;
	}
}
