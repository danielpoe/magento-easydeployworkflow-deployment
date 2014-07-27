<?php

namespace EasyDeployWorkflows\Workflows\Varnish;

use EasyDeployWorkflows\Workflows as Workflows;

class VarnishConfigurationWorkflow extends Workflows\TaskBasedWorkflow {

	/**
	 * @var \EasyDeployWorkflows\Workflows\Varnish\VarnishConfigurationConfiguration
	 */
	protected $workflowConfiguration;

	/**
	 * Can be used to do individual workflow initialisation and/or checks
	 */
	protected function workflowInitialisation() {

		$deploymentSource = $this->replaceMarkers( $this->workflowConfiguration->getDeploymentSource() );
		$localDownloadTargetFolder = $this->getFinalDeliveryFolder();

		$this->addTask('check that we are on correct deploy node',new \EasyDeployWorkflows\Tasks\Common\CheckCorrectDeployNode());


		$downloadTask = new \EasyDeployWorkflows\Tasks\Common\SourceEvaluator();
		$downloadTask->addServerByName('localhost');
		$downloadTask->setSource( $deploymentSource );
		$downloadTask->setParentFolder( $localDownloadTargetFolder );
		$downloadTask->setDeleteBeforeDownload(true);
		$this->addTask('Download Base Varnish Configuration', $downloadTask);



		$appendContentTask =  new \EasyDeployWorkflows\Tasks\Common\AppendContent();
		$appendContentTask->addServerByName('localhost');
		$appendContentTask->setFile( $localDownloadTargetFolder.$this->getFilenameFromPath($deploymentSource) );
		$content = '';
		foreach ($this->workflowConfiguration->getDirectors() as $director) {
			/** @var $director \EasyDeployWorkflows\Varnish\AbstractDirector */
			$content .= $director->generateCode(). PHP_EOL;
		}
		$appendContentTask->setContent($content);
		$this->addTask('Add Backend Directors to Varnish Configuration', $appendContentTask);



		$copyTask = new \EasyDeployWorkflows\Tasks\Common\CopyLocalFile();
		$copyTask->addServersByName($this->workflowConfiguration->getVarnishServers());
		$copyTask->setFrom( $localDownloadTargetFolder.$this->getFilenameFromPath($deploymentSource) );
		$copyTask->setTo( '/tmp/' );
		$copyTask->setDeleteBeforeDownload(true);
		$this->addTask('Copy Final Varnish Conf to Varnish Servers',	$copyTask);

		$tmpWarLocation 			= '/tmp/'.$this->getFilenameFromPath($deploymentSource);

		if ($this->workflowConfiguration->getDeployCommand() != '') {
			$deployTask = new \EasyDeployWorkflows\Tasks\Common\RunCommand();
			$deployTask->addServersByName($this->workflowConfiguration->getVarnishServers());
			$deployTask->setCommand($this->replaceMarkers(sprintf($this->workflowConfiguration->getDeployCommand(),$tmpWarLocation)));
			$this->addTask('Deploy Varnish Conf to Varnish Servers with DeployCommand',	$deployTask);
		}
		else {
			$deployTask = new \EasyDeployWorkflows\Tasks\Common\CopyFile();
			$deployTask->addServersByName($this->workflowConfiguration->getVarnishServers());
			$deployTask->setSourceFile($tmpWarLocation);
			$deployTask->setTargetFile($this->replaceMarkers($this->workflowConfiguration->getTargetVarnishConfigurationFile()));
			$this->addTask('Deploy Varnish Conf to Varnish Servers by copying',	$deployTask);
		}


		$deleteFileTask = new \EasyDeployWorkflows\Tasks\Common\DeleteFile();
		$deleteFileTask->addServersByName($this->workflowConfiguration->getVarnishServers());
		$deleteFileTask->setFile($tmpWarLocation);
		$this->addTask('Delete Tmp Varnish Conf on Varnish Servers',	$deleteFileTask);

		$restartTask = new \EasyDeployWorkflows\Tasks\Common\RunCommand();
		$restartTask->addServersByName($this->workflowConfiguration->getVarnishServers());
		$restartTask->setCommand($this->replaceMarkers($this->workflowConfiguration->getRestartCommand()));
		$this->addTask('Restart Varnish',	$restartTask);

	}
}
