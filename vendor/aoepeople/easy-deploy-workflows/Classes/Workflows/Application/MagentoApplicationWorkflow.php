<?php

namespace EasyDeployWorkflows\Workflows\Application;

use EasyDeployWorkflows\Tasks\Common\RunCommand;
use EasyDeployWorkflows\Tasks\Common\WriteVersionFile;
use EasyDeployWorkflows\Workflows as Workflows;

class MagentoApplicationWorkflow extends ReleaseFolderApplicationWorkflow {

	/**
	 * @var MagentoApplicationConfiguration
	 */
	protected $workflowConfiguration;

	/**
	 * Add version file write
	 */
	protected function addWriteVersionFileTask() {
		$task = new WriteVersionFile();
		$task->setTargetPath($this->getFinalReleaseBaseFolder() . 'next/htdocs');
		$task->setVersion($this->workflowConfiguration->getReleaseVersion());
		$task->addServersByName($this->workflowConfiguration->getInstallServers());
		$this->addTask('Write Version File', $task);
	}

	/**
	 * Symlink media folder
	 */
	protected function addSymlinkSharedFoldersTasks() {
		if ($this->workflowConfiguration->hasSharedFolder()) {
			$sharedFolder = $this->replaceMarkers($this->workflowConfiguration->getSharedFolder());
			if (!empty($sharedFolder)) {
				$task = new RunCommand();
				$task->setChangeToDirectory($this->getFinalReleaseBaseFolder() . 'next/htdocs');
				$task->setCommand('rm -rf media && ln -s ' . $sharedFolder . 'media media');
				$task->addServersByName($this->workflowConfiguration->getInstallServers());
				$this->addTask('Link shared folder', $task);
			}
		}
	}

	/**
	 * See if commandline indexer can return a status
	 *
	 * @param array $additionalTasks
	 */
	protected function addSmokeTestTaskGroup($additionalTasks = array()) {
		// add default smoke test
		$task = new RunCommand();
		$task->setChangeToDirectory($this->getFinalReleaseBaseFolder() . 'next');
		$task->setCommand('php htdocs/shell/indexer.php status');
		$task->addServersByName($this->workflowConfiguration->getInstallServers());
		$additionalTasks['Smoke Test - call indexer.php status'] = $task;

		parent::addSmokeTestTaskGroup($additionalTasks);
	}

	/**
	 * Possibility to add some tasks
	 */
	protected function addPostSetupTasks() {
		$task = new RunCommand();
		$task->setChangeToDirectory($this->getFinalReleaseBaseFolder() . 'next');
		$task->setCommand('php htdocs/shell/indexer.php --reindexall');
		$task->addServersByName($this->workflowConfiguration->getInstallServers());

		switch ($this->workflowConfiguration->getReindexAllMode()) {
			case MagentoApplicationConfiguration::REINDEX_MODE_NONE:
				break;
			case MagentoApplicationConfiguration::REINDEX_MODE_FOREGROUND:
				$this->addTask('Reindex all in foreground', $task);
				break;
			case MagentoApplicationConfiguration::REINDEX_MODE_BACKGROUND:
				$task->setRunInBackground(true);
				$this->addTask('Reindex all in background', $task);
				break;
		}
	}

}
