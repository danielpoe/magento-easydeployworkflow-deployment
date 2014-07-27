<?php

namespace EasyDeployWorkflows\Workflows\Application;

use EasyDeployWorkflows\Tasks\AbstractTask;
use EasyDeployWorkflows\Workflows as Workflows;
use EasyDeployWorkflows\Workflows\Exception as Exception;


/**
 * Configuration for the Basic Application Workflow
 */
abstract class AbstractBaseApplicationConfiguration extends Workflows\AbstractWorkflowConfiguration {

	/**
	 * Command for configuring the application
	 *
	 * @var string
	 */
	protected $setupCommand;

	/**
	 * @var AbstractTask[]
	 */
	protected $preSetupTasks = array();

	/**
	 * @var AbstractTask[]
	 */
	protected $postSetupTasks = array();

	/**
	 * @var AbstractTask[]
	 */
	protected $smokeTestTasks = array();

	/**
	 * @param string $setupCommand
	 * @return $this
	 */
	public function setSetupCommand($setupCommand) {
		$this->setupCommand = $setupCommand;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSetupCommand() {
		return $this->setupCommand;
	}

	/**
	 * @param string $name
	 * @param AbstractTask $step
	 * @throws Exception\DuplicateStepAssignmentException
	 */
	public function addPreSetupTask($name, AbstractTask $step) {
		if (isset($this->preSetupTasks[$name])) {
			throw new Exception\DuplicateStepAssignmentException($name . ' already exists!');
		}
		$step->validate();
		$this->preSetupTasks[$name] = $step;
	}

	/**
	 * @param $name
	 * @param AbstractTask $step
	 * @throws Exception\DuplicateStepAssignmentException
	 */
	public function addPostSetupTask($name, AbstractTask $step) {
		if (isset($this->postSetupTasks[$name])) {
			throw new Exception\DuplicateStepAssignmentException($name . ' already exists!');
		}
		$step->validate();
		$this->postSetupTasks[$name] = $step;
	}

	/**
	 * @param string $name
	 * @param AbstractTask $task
	 * @throws Exception\DuplicateStepAssignmentException
	 */
	public function addSmokeTestTask($name, AbstractTask $task) {
		if (isset($this->smokeTestTasks[$name])) {
			throw new Exception\DuplicateStepAssignmentException($name . ' already exists!');
		}
		$task->validate();
		$this->smokeTestTasks[$name] = $task;
	}

	/**
	 * @return AbstractTask[]
	 */
	public function getPreSetupTasks() {
		return $this->preSetupTasks;
	}

	/**
	 * @return AbstractTask[]
	 */
	public function getPostSetupTasks() {
		return $this->postSetupTasks;
	}

	/**
	 * @return AbstractTask[]
	 */
	public function getSmokeTestTasks() {
		return $this->smokeTestTasks;
	}

	/**
	 * @return array
	 */
	public function getInstallServers() {
		return $this->getServers('installserver');
	}

	/**
	 * @return bool
	 */
	public function hasInstallServers() {
		return count($this->getInstallServers()) > 0;
	}

	/**
	 * @param string $hostName
	 * @return $this
	 */
	public function addInstallServer($hostName) {
		$this->addServer($hostName, 'installserver');

		return $this;
	}

}
