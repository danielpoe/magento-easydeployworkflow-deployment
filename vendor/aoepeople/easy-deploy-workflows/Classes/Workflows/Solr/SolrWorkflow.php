<?php

namespace EasyDeployWorkflows\Workflows\Solr;

use EasyDeployWorkflows\Workflows as Workflows;

class SolrWorkflow extends Workflows\AbstractWorkflow {

	/**
	 * @var \EasyDeployWorkflows\Workflows\Solr\SolrConfiguration
	 */
	protected $workflowConfiguration;

	/**
	 * @param string $releaseVersion
	 * @return mixed|void
	 */
	public function deploy() {
		$this->logger->log('[Workflow] SolrWorkflow');
		$this->logger->addLogIndentLevel();

		try {
			$this->runDeployTasks();
		}
		catch (\Exception $e) {
			$this->logger->log('[TASK EXCEPTION] '.$e->getMessage(),\EasyDeployWorkflows\Logger\Logger::MESSAGE_TYPE_ERROR);
			$this->logger->printLogFileInfoMessage();
			throw new \EasyDeployWorkflows\Exception\HaltAndRollback($taskName.' failed with message: "'.$e->getMessage().'"');
		}

		$this->logger->log('[Workflow Successful]',\EasyDeployWorkflows\Logger\Logger::MESSAGE_TYPE_SUCCESS);
		$this->logger->removeLogIndentLevel();

	}

	public function runDeployTasks()
	{
		$deliveryFolder = $this->replaceMarkers($this->getFinalDeliveryFolder());
		$packageSource = $this->replaceMarkers($this->workflowConfiguration->getSource());

		$this->logger->log('[Task] CheckCorrectDeployNode');
		$task = new \EasyDeployWorkflows\Tasks\Common\CheckCorrectDeployNode();
		$task->run($this->createTaskRunInformation());

		$this->logger->log('[Task] Download Package local');
		$task = new \EasyDeployWorkflows\Tasks\Common\SourceEvaluator();
		$task->addServer($this->getServer('localhost'));
		$task->setSource($packageSource);
		$task->setParentFolder($deliveryFolder);
		$task->run($this->createTaskRunInformation());
		$this->logger->log('[Task Successful]', \EasyDeployWorkflows\Logger\Logger::MESSAGE_TYPE_SUCCESS);

		$packageFileName = $this->getFilenameFromPath($packageSource);

		$this->logger->log('[Task] (Up)load Package to solr(s)');
		$task = new \EasyDeployWorkflows\Tasks\Common\CopyLocalFile();
		$task->addServersByName($this->workflowConfiguration->getMasterServers());
		$task->setFrom($deliveryFolder.$packageFileName);
		if ($this->workflowConfiguration->hasTempDeliverFolder()) {
			$targetFolderForSolrPackageOnSolrServer = $this->workflowConfiguration->getTempDeliverFolder();
		}
		else {
			$targetFolderForSolrPackageOnSolrServer = $deliveryFolder;
		}
		$task->setTo($targetFolderForSolrPackageOnSolrServer);
		$task->run($this->createTaskRunInformation());
		$this->logger->log('[Task Successful]', \EasyDeployWorkflows\Logger\Logger::MESSAGE_TYPE_SUCCESS);

		$packageFileName = $this->getFilenameFromPath($this->replaceMarkers($this->workflowConfiguration->getSource()->getFilename()));
		$this->logger->log('[Task] Unzip Solr Package');
		$task = new \EasyDeployWorkflows\Tasks\Common\Untar();
		$task->addServersByName($this->workflowConfiguration->getMasterServers());
		$task->autoInitByPackagePath($targetFolderForSolrPackageOnSolrServer . '/' . $packageFileName);
		$task->setMode(\EasyDeployWorkflows\Tasks\Common\Untar::MODE_SKIP_IF_EXTRACTEDFOLDER_EXISTS);
		$task->run($this->createTaskRunInformation());
		$this->logger->log('[Task Successful]', \EasyDeployWorkflows\Logger\Logger::MESSAGE_TYPE_SUCCESS);

		$this->logger->log('[Task] Deploy Solr Package');
		$task = new \EasyDeployWorkflows\Tasks\Web\RunPackageInstallBinaries();
		$task->addServersByName($this->workflowConfiguration->getMasterServers());
		$task->setTargetSystemPath($this->replaceMarkers($this->workflowConfiguration->getInstancePath()));
		$task->setSilentMode($this->workflowConfiguration->getInstallSilent());
		$task->setPackageFolder($targetFolderForSolrPackageOnSolrServer . '/' . $this->getFileBaseName($packageFileName));
		$task->setNeedBackupToInstall(FALSE);
		$task->run($this->createTaskRunInformation());
		$this->logger->log('[Task Successful]', \EasyDeployWorkflows\Logger\Logger::MESSAGE_TYPE_SUCCESS);


		$restartCommand = $this->workflowConfiguration->getRestartCommand();
		if (empty($restartCommand) == '') {
			$this->logger->log('No restart Command is Set for the deployment!', \EasyDeployWorkflows\Logger\Logger::MESSAGE_TYPE_WARNING);
		} else {
			$this->logger->log('[Task] Reload Solrs');
			$task = new \EasyDeployWorkflows\Tasks\Common\RunScript();
			$task->setScript($restartCommand);
			$task->run($this->createTaskRunInformation());
			$this->logger->log('[Task Successful]', \EasyDeployWorkflows\Logger\Logger::MESSAGE_TYPE_SUCCESS);
		}
	}

	protected function getFileBaseName($filename) {
		return substr($filename,0,strpos($filename,'.'));
	}
}