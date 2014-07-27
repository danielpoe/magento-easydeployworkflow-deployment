<?php

namespace EasyDeployWorkflows\Workflows\Servlet;

use EasyDeployWorkflows\Workflows as Workflows;

class ServletWorkflow extends Workflows\TaskBasedWorkflow {

	/**
	 * @var \EasyDeployWorkflows\Workflows\Servlet\ServletConfiguration
	 */
	protected $workflowConfiguration;

	/**
	 * @var string
	 */
	const CURL_DEPLOY_COMMAND = 'curl --upload-file %s -u %s "http://localhost:%s/manager/deploy?path=%s&update=true"';


	/**
	 * Can be used to do individual workflow initialisation and/or checks
	 */
	protected function workflowInitialisation() {

		$this->addTask('check that we are on correct deploy node',new \EasyDeployWorkflows\Tasks\Common\CheckCorrectDeployNode());


		$downloadTask = new \EasyDeployWorkflows\Tasks\Common\SourceEvaluator();
		$downloadTask->addServerByName('localhost');
		$downloadTask->setSource( $this->workflowConfiguration->getSource() );
		$downloadTask->setParentFolder( $this->getFinalDeliveryFolder() );
		$this->addTask('Download tracker war to local delivery folder', $downloadTask);

		$copyTask = new \EasyDeployWorkflows\Tasks\Common\CopyLocalFile();
		$copyTask->addServersByName($this->workflowConfiguration->getServletServers());
		$copyTask->setFrom( $this->getFinalDeliveryFolder().$this->workflowConfiguration->getSource()->getFileName() );
		$copyTask->setTo( '/tmp/' );
		$copyTask->setDeleteBeforeDownload(true);
		$this->addTask('Load tracker war to tmp folder on servlet servers',	$copyTask);

		$tmpWarLocation 			= '/tmp/'.$this->workflowConfiguration->getSource()->getFileName();
		$deployWarTask = new \EasyDeployWorkflows\Tasks\Servlet\DeployWarInTomcat();
		$deployWarTask->addServersByName($this->workflowConfiguration->getServletServers());
		$deployWarTask->setWarFileSourcePath( $tmpWarLocation );
		$deployWarTask->setTomcatPassword( $this->workflowConfiguration->getTomcatPassword() );
		$deployWarTask->setTomcatUser( $this->workflowConfiguration->getTomcatUsername() );
		$deployWarTask->setTomcatPath( $this->workflowConfiguration->getTargetPath() );
		$deployWarTask->setTomcatPort( $this->workflowConfiguration->getTomcatPort() );
		$deployWarTask->setTomcatVersion( $this->workflowConfiguration->getTomcatVersion() );

		$this->addTask('deploy the war file to the tomcat servers',$deployWarTask);

	}
}