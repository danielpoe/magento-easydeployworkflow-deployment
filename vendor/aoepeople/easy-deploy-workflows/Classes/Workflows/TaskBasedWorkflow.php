<?php

namespace EasyDeployWorkflows\Workflows;

use EasyDeployWorkflows\Exception\HaltAndRollback;
use EasyDeployWorkflows\Logger\Logger;
use EasyDeployWorkflows\Tasks\AbstractTask;
use EasyDeployWorkflows\Tasks\Common\TaskGroup;
use EasyDeployWorkflows\Workflows;

require_once dirname(__FILE__) . '/Exception/DuplicateStepAssignmentException.php';

class TaskBasedWorkflow extends AbstractWorkflow {

	/**
	 * @var AbstractTask[]
	 */
	protected $tasks = array();

	/**
	 * @param string $name
	 * @param AbstractTask $task
	 * @throws Exception\DuplicateStepAssignmentException
	 */
	public function addTask($name, AbstractTask $task) {
		if (isset($this->tasks[$name])) {
			throw new Exception\DuplicateStepAssignmentException($name . ' already exists!');
		}
		$task->validate();
		$this->tasks[$name] = $task;
	}

	public function deploy() {
		$taskRunInformation = $this->createTaskRunInformation();
		$this->logger->log('[Workflow] ' . $this->getWorkflowConfiguration()->getTitle() . ' (' . get_class($this) . ')');
		$this->logger->addLogIndentLevel();
		foreach ($this->tasks as $taskName => $task) {
			if ($task instanceof TaskGroup) {
				$this->logger->logDivider($task->getHeadline());
			} else {
				$this->logger->log('[Task] ' . $taskName);
			}
			$this->logger->addLogIndentLevel();
			try {
				$task->run($taskRunInformation);
				$this->logger->log('[Task Successful]', Logger::MESSAGE_TYPE_SUCCESS);
			} catch (\Exception $e) {
				$this->logger->log('[TASK EXCEPTION] ' . $e->getMessage(), Logger::MESSAGE_TYPE_ERROR);
				$this->logger->logToFile('Exception Details: ' . $e->getFile() . PHP_EOL . $e->getLine() . PHP_EOL . $e->getTraceAsString(), Logger::MESSAGE_TYPE_ERROR);
				$this->logger->printLogFileInfoMessage();
				throw new HaltAndRollback($taskName . ' failed with message: "' . $e->getMessage() . '"');
			}
			$this->logger->removeLogIndentLevel();
		}
		$this->logger->log(PHP_EOL . '[Workflow Successful]' . PHP_EOL, Logger::MESSAGE_TYPE_SUCCESS);
		$this->logger->removeLogIndentLevel();
	}

	/**
	 * @param string $name
	 * @return AbstractTask
	 * @throws \Exception
	 */
	public function getTaskByName($name) {
		if (!isset($this->tasks[$name])) {
			throw new \Exception("Task with name " . $name . " doesn't exists");
		}

		return $this->tasks[$name];
	}

	/**
	 * @return AbstractTask[]
	 */
	public function getTasks() {
		return $this->tasks;
	}
}
