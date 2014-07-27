<?php

namespace EasyDeployWorkflows\Workflows\Application;

use EasyDeployWorkflows\Workflows as Workflows;

class StandardApplicationWorkflow extends BaseApplicationWorkflow {

	/**
	 * @var StandardApplicationConfiguration
	 */
	protected $workflowConfiguration;

	/**
	 * Can be used to do individual workflow initialisation and/or checks
	 */
	protected function workflowInitialisation() {

		$this->addTask('check correct deploy node',
			new \EasyDeployWorkflows\Tasks\Common\CheckCorrectDeployNode());

		$this->addPreSetupTasks();

		$task = $this->getDownloadPackageTask();
		$task->addServersByName($this->workflowConfiguration->getInstallServers());
		$this->addTask('Download Package',$task	);

		$task = $this->getUnzipPackageTask();
		$task->addServersByName($this->workflowConfiguration->getInstallServers());
		$this->addTask('Untar Package', $task);

		$extractedFolder = $this->replaceMarkers($this->getFinalDeliveryFolder() . $this->workflowConfiguration->getSource()->getFileNameWithOutExtension());

		$task = $this->getInstallPackageTask($extractedFolder);
		$task->addServersByName($this->workflowConfiguration->getInstallServers());
		$this->addTask('Install Package', $task);

		$this->addPostSetupTaskGroup();

		$task = $this->getCleanupTask($extractedFolder);
		$task->addServersByName($this->workflowConfiguration->getInstallServers());
		$this->addTask('Cleanup Archive',$task);

	}

	/**
	 * Installation of Magento
	 *
	 * @return void
	 */
	protected function addSetupTasks()
	{
		$task = new \EasyDeployWorkflows\Tasks\Common\RunCommand();


		$task->addServersByName($this->workflowConfiguration->getInstallServers());
		$this->addTask('Setup Script',$task);
	}

	/**
	 * Installation is simple copy
	 *
	 * @return \EasyDeployWorkflows\Tasks\Web\RunPackageInstallBinaries
	 */
	protected function getInstallPackageTask($extractedFolder)
	{
		$task = new \EasyDeployWorkflows\Tasks\Common\RunCommand();
		$task->setChangeToDirectory($extractedFolder);

		$command = $this->replaceMarkers($this->workflowConfiguration->getSetupCommand());
		$command = str_replace('###targetfolder###',$this->replaceMarkers($this->workflowConfiguration->getInstallationTargetFolder()),$command);
		$task->setCommand($command);

		return $task;
	}

	/**
	 * Installation is simple copy
	 *
	 * @return \EasyDeployWorkflows\Tasks\Common\DeleteFolder
	 */
	protected function getCleanupTask($extractedFolder)
	{
		$step = new \EasyDeployWorkflows\Tasks\Common\DeleteFolder();
		$step->setFolder($extractedFolder);
		return $step;
	}



	protected function getDownloadPackageTask()
	{
		$step = new \EasyDeployWorkflows\Tasks\Common\SourceEvaluator();
		$step->setSource($this->workflowConfiguration->getSource());
		$step->setNotIfPathExists($this->getFinalDeliveryFolder().$this->workflowConfiguration->getSource()->getFileName());
		$step->setParentFolder($this->getFinalDeliveryFolder());
		$step->setNotIfPathExists($this->getFinalDeliveryFolder() . $this->workflowConfiguration->getSource()->getFileName());
		return $step;
	}


	protected function getUnzipPackageTask()
	{
		$step = new \EasyDeployWorkflows\Tasks\Common\Untar();
		$step->autoInitByPackagePath($this->getFinalDeliveryFolder() . '/' . $this->workflowConfiguration->getSource()->getFileName());
		$step->setMode(\EasyDeployWorkflows\Tasks\Common\Untar::MODE_SKIP_IF_EXTRACTEDFOLDER_EXISTS);
		return $step;
	}


}