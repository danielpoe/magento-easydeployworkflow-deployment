<?php

namespace EasyDeployWorkflows\Workflows\Application;

use EasyDeployWorkflows\Exception\InvalidConfigurationException;
use EasyDeployWorkflows\Source\File\FileSourceInterface;
use EasyDeployWorkflows\Source\Folder\FolderSourceInterface;
use EasyDeployWorkflows\Workflows as Workflows;
use EasyDeployWorkflows\Workflows\Exception as Exception;


/**
 * Configuration for the Basic Application Workflow
 *
 * @method FileSourceInterface|FolderSourceInterface getSource()
 */
class ReleaseFolderApplicationConfiguration extends AbstractBaseApplicationConfiguration {

	/**
	 * @param string $folder
	 * @return $this
	 */
	public function setReleaseBaseFolder($folder)
	{
		$this->setFolder($folder, 'ReleaseBaseFolder', 0);

		return $this;
	}

	/**
	 * @return string
	 */
	public function getReleaseBaseFolder()
	{
		return $this->getFolder('ReleaseBaseFolder', 0);
	}

	/**
	 * @param string $folder
	 * @return $this
	 */
	public function setSharedFolder($folder)
	{
		$this->setFolder($folder, 'SharedFolder', 0);

		return $this;
	}

	/**
	 * @return $this
	 */
	public function unsetSharedFolder()
	{
		$this->unsetFolder('SharedFolder', 0);

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSharedFolder()
	{
		return $this->getFolder('SharedFolder', 0);
	}

	/**
	 * @return boolean
	 */
	public function hasSharedFolder()
	{
		return $this->getSharedFolder() != '';
	}

	/**
	 * @return string
	 */
	public function getWorkflowClassName() {
		return 'EasyDeployWorkflows\Workflows\Application\ReleaseFolderApplicationWorkflow';
	}

	/**
	 * @throws InvalidConfigurationException
	 * @return bool
	 */
	public function validate() {
		if(!$this->getReleaseBaseFolder() !='') {
			throw new InvalidConfigurationException("Please configure ReleaseBaseFolder: ".get_class($this));
		}

		if (!$this->hasSource()) {
			throw new InvalidConfigurationException("No download Source given: ".get_class($this));
		}

		return true;
	}

}
