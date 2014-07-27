<?php

namespace EasyDeployWorkflows\Workflows\Application;

use EasyDeployWorkflows\Tasks\AbstractServerTask;
use EasyDeployWorkflows\Tasks\Common\RunCommand;
use EasyDeployWorkflows\Tasks\Common\TaskGroup;
use EasyDeployWorkflows\Tasks\Common\WriteVersionFile;
use EasyDeployWorkflows\Workflows as Workflows;

class BaseApplicationWorkflow extends Workflows\TaskBasedWorkflow {

	/**
	 * @var AbstractBaseApplicationConfiguration
	 */
	protected $workflowConfiguration;

	/**
	 * Possibility to add some tasks
	 */
	protected function addPreSetupTasks() {
		foreach ($this->workflowConfiguration->getPreSetupTasks() as $name => $task) {
			$this->addTask($name, $task);
		}
	}

	/**
	 * @param string $targetPathForVersionFile
	 * @return WriteVersionFile
	 */
	protected function getWriteVersionFileTask($targetPathForVersionFile) {
		$task = new WriteVersionFile();
		$task->setTargetPath($targetPathForVersionFile);
		$task->setVersion($this->workflowConfiguration->getReleaseVersion());

		return $task;
	}

	/**
	 * Installation of the application
	 *
	 * @param string $applicationRootFolder
	 * @return RunCommand
	 */
	protected function getSetupTask($applicationRootFolder) {
		$task = new RunCommand();
		$task->setChangeToDirectory($applicationRootFolder);
		$command = $this->replaceMarkers($this->workflowConfiguration->getSetupCommand());
		$task->setCommand($command);

		return $task;
	}

	/**
	 * Possibility to add some tasks
	 */
	protected function addPostSetupTaskGroup() {
		$this->addTask('Post Setup', $this->getTaskGroup('Post Setup',
			$this->workflowConfiguration->getPostSetupTasks())
		);
	}

	/**
	 * @TODO
	 */
	protected function addPostSwitchTasks() {

	}

	/**
	 * @param string $headline
	 * @param array $tasks
	 * @throws \EasyDeployWorkflows\Workflows\Exception\DuplicateStepAssignmentException
	 * @return TaskGroup
	 */
	protected function getTaskGroup($headline, array $tasks) {
		$taskGroup = new TaskGroup();
		$taskGroup->setHeadline($headline);
		// add defined tasks
		foreach ($tasks as $description => $task) {
			if ($task instanceof AbstractServerTask) {
				$task = $this->prepareTask($task);
			}
			$taskGroup->addTask($description, $task);
		}
		return $taskGroup;
	}

	/**
	 * @param AbstractServerTask $task
	 * @return AbstractServerTask
	 */
	protected function prepareTask(AbstractServerTask $task) {
		$task->addServersByName($this->workflowConfiguration->getInstallServers());
		return $task;
	}

}
