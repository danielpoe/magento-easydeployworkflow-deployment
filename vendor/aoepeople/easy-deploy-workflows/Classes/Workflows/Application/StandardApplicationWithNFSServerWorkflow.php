<?php

namespace EasyDeployWorkflows\Workflows\Application;

use EasyDeployWorkflows\Workflows as Workflows;

class StandardApplicationWithNFSServerWorkflow extends StandardApplicationWorkflow {

	/**
	 * @var StandardApplicationWithNFSServerConfiguration
	 */
	protected $workflowConfiguration;

	/**
	 * Can be used to do individual workflow initialisation and/or checks
	 */
	protected function workflowInitialisation() {

		$this->addTask('check correct deploy node',
						new \EasyDeployWorkflows\Tasks\Common\CheckCorrectDeployNode());

		$task = $this->getDownloadPackageTask();
		$task->addServerByName($this->workflowConfiguration->getNFSServer());
		$this->addTask('Download Package',$task	);

		$task = $this->getUnzipPackageTask();
		$task->addServerByName($this->workflowConfiguration->getNFSServer());
		$this->addTask('Untar Package', $task);

		$extractedFolder = $this->replaceMarkers($this->getFinalDeliveryFolder() . $this->workflowConfiguration->getSource()->getFileNameWithOutExtension());

		$task = $this->getInstallPackageTask($extractedFolder);
		$task->addServerByName($this->workflowConfiguration->getNFSServer());
		$this->addTask('Install Package', $task);



		if ($this->workflowConfiguration->hasSyncFromNFSScript()) {
			$task = $this->getRunNFSSyncScriptTask();
			$task->addServersByName($this->workflowConfiguration->getInstallServers());
			$this->addTask('Sync from NFS', $task);
		}
		$task = $this->getCleanupTask($extractedFolder);
		$task->addServerByName($this->workflowConfiguration->getNFSServer());
		$this->addTask('Cleanup Archive',$task);

	}

	/**
	 * @return \EasyDeployWorkflows\Tasks\Common\RunScript
	 */
	protected function getRunNFSSyncScriptTask()
	{
		$step = new \EasyDeployWorkflows\Tasks\Common\RunScript();
		$step->setScript($this->workflowConfiguration->getSyncFromNFSScript());
		$step->addServersByName($this->workflowConfiguration->getNfsServer());
		$step->setIsOptional(true);
		return $step;
	}




}