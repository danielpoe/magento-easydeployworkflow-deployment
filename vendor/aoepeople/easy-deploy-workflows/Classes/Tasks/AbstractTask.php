<?php

namespace EasyDeployWorkflows\Tasks;

use EasyDeployWorkflows\AbstractPart;
use EasyDeployWorkflows\Exception\InvalidConfigurationException;
use EasyDeployWorkflows\ValidateableInterface;
use EasyDeployWorkflows\Workflows;


/**
 * A task is something that encapsulates a certain part of todo
 */
abstract class AbstractTask extends AbstractPart implements ValidateableInterface {

	/**
	 * @return boolean
	 */
	public function isValid() {
		try {
			$this->validate();
		} catch (InvalidConfigurationException $e) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * @param string $string
	 * @param TaskRunInformation $taskRunInformation
	 * @return string
	 */
	protected function replaceConfigurationMarkersWithTaskRunInformation($string,
		TaskRunInformation $taskRunInformation
	) {
		return $this->replaceConfigurationMarkers($string, $taskRunInformation->getWorkflowConfiguration(),
			$taskRunInformation->getInstanceConfiguration()
		);
	}

	/**
	 * @return boolean
	 * @throws InvalidConfigurationException
	 */
	public function validate() {
		throw new InvalidConfigurationException('IMPLEMENT YOUR OWN VALIDATION');
	}

	/**
	 * @param TaskRunInformation $taskRunInformation
	 * @return mixed
	 */
	abstract public function run(TaskRunInformation $taskRunInformation);

}
