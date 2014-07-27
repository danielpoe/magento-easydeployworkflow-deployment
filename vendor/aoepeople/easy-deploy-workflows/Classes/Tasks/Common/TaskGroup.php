<?php

namespace EasyDeployWorkflows\Tasks\Common;


use EasyDeployWorkflows\Logger\Logger;
use EasyDeployWorkflows\Tasks\AbstractTask;
use EasyDeployWorkflows\Workflows\Exception\DuplicateStepAssignmentException;
use EasyDeployWorkflows\Tasks\TaskRunInformation;

class TaskGroup extends AbstractTask {

	/**
	 * @var AbstractTask[]
	 */
	protected $tasks = array();

	/**
	 * @var string
	 */
	protected $headline = '';

	/**
	 * @param string $headline
	 */
	public function setHeadline($headline) {
		$this->headline = $headline;
	}

	/**
	 * @return string
	 */
	public function getHeadline() {
		return $this->headline;
	}

	/**
	 * @param string $name
	 * @param AbstractTask $task
	 * @throws DuplicateStepAssignmentException
	 */
	public function addTask($name, AbstractTask $task) {
		if (isset($this->tasks[$name])) {
			throw new DuplicateStepAssignmentException($name . ' already exists!');
		}
		$task->validate();
		$this->tasks[$name] = $task;
	}

	/**
	 * @param TaskRunInformation $taskRunInformation
	 * @return mixed
	 */
	public function run(TaskRunInformation $taskRunInformation) {
		if (empty($this->tasks)) {
			$this->logger->log('No Tasks');
		}
		foreach ($this->tasks as $taskName => $task) {
			$this->logger->log('[Task] ' . $taskName);
			$this->logger->addLogIndentLevel();
			$task->run($taskRunInformation);
			$this->logger->log('[Task Successful]', Logger::MESSAGE_TYPE_SUCCESS);
			$this->logger->removeLogIndentLevel();
		}
	}

	/**
	 * @return bool
	 */
	public function validate() {
		return true;
	}
}
