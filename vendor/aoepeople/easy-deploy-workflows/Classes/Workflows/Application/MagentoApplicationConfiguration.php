<?php

namespace EasyDeployWorkflows\Workflows\Application;

use EasyDeployWorkflows\Workflows as Workflows;
use EasyDeployWorkflows\Workflows\Exception as Exception;

/**
 * Configuration for the Magento Application Workflow
 */
class MagentoApplicationConfiguration extends ReleaseFolderApplicationConfiguration {

	/**
	 * @var string
	 */
	protected $setupCommand = './Setup/Setup.sh';

	/**
	 * @var int
	 */
	protected $reindexAllMode;

	const REINDEX_MODE_NONE = 0;
	const REINDEX_MODE_FOREGROUND = 1;
	const REINDEX_MODE_BACKGROUND = 2;

	/**
	 * @param int $reindexAllMode
	 * @return self
	 */
	public function setReindexAllMode($reindexAllMode) {
		$this->reindexAllMode = $reindexAllMode;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getReindexAllMode() {
		if (!isset($this->reindexAllMode) || !in_array($this->reindexAllMode, array(0, 1, 2))) {
			return self::REINDEX_MODE_NONE;
		}
		return $this->reindexAllMode;
	}

	/**
	 * @return string
	 */
	public function getWorkflowClassName() {
		return 'EasyDeployWorkflows\Workflows\Application\MagentoApplicationWorkflow';
	}

}
