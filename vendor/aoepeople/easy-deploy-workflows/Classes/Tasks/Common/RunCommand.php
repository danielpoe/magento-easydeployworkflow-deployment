<?php

namespace EasyDeployWorkflows\Tasks\Common;

use EasyDeployWorkflows\Exception\InvalidConfigurationException;
use EasyDeployWorkflows\Tasks;
use EasyDeployWorkflows\Tasks\AbstractServerTask;
use EasyDeployWorkflows\Tasks\TaskRunInformation;


class RunCommand extends AbstractServerTask {

	/**
	 * @var string
	 */
	protected $command;

	/**
	 * @param string $script
	 * @return $this
	 */
	public function setCommand($script) {
		$this->command = $script;

		return $this;
	}

	/**
	 * @param TaskRunInformation $taskRunInformation
	 * @param \EasyDeploy_AbstractServer $server
	 * @return mixed
	 */
	protected function runOnServer(TaskRunInformation $taskRunInformation, \EasyDeploy_AbstractServer $server) {
		$command = $this->command;
		$command = $this->_appendRunInBackground($command);
		//$command = $this->_prependWithEnvVarExport($command);

		$environmentVariables = 'export ENVIRONMENT="' . $taskRunInformation->getInstanceConfiguration()->getEnvironmentName() . '"';
		$environmentVariables .= ' && export PROJECTNAME="' . $taskRunInformation->getInstanceConfiguration()->getProjectName() . '"';
		$environmentVariables .= ' && export RELEASEVERSION="' . $taskRunInformation->getWorkflowConfiguration()->getReleaseVersion() . '"';
		$environmentVariables .= ' && export RELEASEVERSION_ESCAPED="' . PREG_REPLACE("/[^0-9a-zA-Z]/i", '', $taskRunInformation->getWorkflowConfiguration()->getReleaseVersion()) . '" && ';

		$command = $environmentVariables . $command;
		$command = $this->_prependWithCd($command, $taskRunInformation);
		$this->executeAndLog($server, $command);
	}

	/**
	 * @return boolean
	 * @throws InvalidConfigurationException
	 */
	public function validate() {
		if (!isset($this->command)) {
			throw new InvalidConfigurationException('Command not set');
		}
		return true;
	}
}
