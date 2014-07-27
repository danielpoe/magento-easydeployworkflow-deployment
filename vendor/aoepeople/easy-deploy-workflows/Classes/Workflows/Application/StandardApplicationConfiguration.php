<?php

namespace EasyDeployWorkflows\Workflows\Application;

use EasyDeployWorkflows\Exception\InvalidConfigurationException;
use EasyDeployWorkflows\Workflows as Workflows;
use EasyDeployWorkflows\Workflows\Exception as Exception;


/**
 * Configuration for the Basic Application Workflow
 */
class StandardApplicationConfiguration extends AbstractBaseApplicationConfiguration {

	/**
	 * @var string
	 */
	protected $setupCommand = 'rsync -az . ###targetfolder###';

	/**
	 * @param string $installationTargetFolder
	 * @return $this
	 */
	public function setInstallationTargetFolder($installationTargetFolder) {
		$this->setFolder($installationTargetFolder, 'InstallationTargetFolder', 0);

		return $this;
	}

	/**
	 * @return string
	 */
	public function getInstallationTargetFolder() {
		return $this->getFolder('InstallationTargetFolder', 0);
	}

	/**
	 * @return bool
	 */
	public function hasInstallationTargetFolder() {
		return $this->getInstallationTargetFolder() != '';
	}

	/**
	 * @return string
	 */
	public function getWorkflowClassName() {
		return 'EasyDeployWorkflows\Workflows\Application\StandardApplicationWorkflow';
	}

	/**
	 * @throws InvalidConfigurationException
	 * @return bool
	 */
	public function validate() {
		if (!$this->hasInstallServers()) {
			throw new InvalidConfigurationException("Please configure at least one server for workflow: " . get_class($this));
		}

		if (!$this->hasInstallationTargetFolder()) {
			throw new InvalidConfigurationException("Please configure the target folder for workflow: " . get_class($this));
		}

		if (!$this->hasSource()) {
			throw new InvalidConfigurationException("No download Source given: " . get_class($this));
		}

		return true;
	}

}
