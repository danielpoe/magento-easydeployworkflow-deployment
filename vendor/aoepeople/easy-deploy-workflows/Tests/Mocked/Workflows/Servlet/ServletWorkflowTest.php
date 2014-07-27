<?php

use EasyDeployWorkflows\Workflows\Servlet as Servlet;
use EasyDeployWorkflows\Workflows as Workflows;

require_once EASYDEPLOY_WORKFLOW_ROOT . 'Classes/Autoloader.php';
require_once EASYDEPLOY_WORKFLOW_ROOT . 'Tests/Mocked/AbstractMockedTest.php';

class ServletWorkflowTest extends AbstractMockedTest {

	/**
	 *
	 * @test
	 * @return void
	 */
	public function canDeployToTwoTomcatServers() {
		$this->requireEasyDeployClassesOrSkip();

		$workflowConfiguration = new Servlet\ServletConfiguration();
		$instanceConfiguration = new Workflows\InstanceConfiguration();

		$workflowConfiguration
				->addServletServer('solr1.company.com')
				->addServletServer('solr2.company.com')
				->setTomcatPort(8080)
				->setTomcatUsername('foo')
				->setTomcatPassword('bar')
				->setTomcatVersion('6')
				->setSource(new EasyDeployWorkflows\Source\File\DownloadSource('http://www.test.com/homer.simpson/###releaseversion###/somedownloadpackage.tar.gz'))
				->setInstallSilent(false)
				->setReleaseVersion('4711')
				->setDeliveryFolder('/home/download/###projectname###/###releaseversion###');


		$instanceConfiguration
				->setProjectName('nasa')
				->addAllowedDeployServer('allowedserver')
				->setEnvironmentName('deploy');

			/** @var $workflow  EasyDeployWorkflows\Workflows\Servlet\ServletWorkflow */
		$workflow = new EasyDeployWorkflows\Workflows\Servlet\ServletWorkflow($instanceConfiguration,$workflowConfiguration);
		$this->assertEquals(count($workflow->getTasks()),4,'expected 4 tasks in the workflow');


		//First task downloads from correct url
		$dowloadFromCiServerTask = $workflow->getTaskByName('Download tracker war to local delivery folder');
		$this->assertEquals(1,count($dowloadFromCiServerTask->getServers()));
		$this->assertTrue($dowloadFromCiServerTask instanceof EasyDeployWorkflows\Tasks\Common\SourceEvaluator);
		$this->assertEquals('cd /;wget http://www.test.com/homer.simpson/###releaseversion###/somedownloadpackage.tar.gz', $dowloadFromCiServerTask->getSource()->getDownloadCommand('/'));
		$this->assertEquals('/home/download/nasa/4711/', $dowloadFromCiServerTask->getParentFolder());

		//second uploads to servlet servers
		$uploadToServletServersTask = $workflow->getTaskByName('Load tracker war to tmp folder on servlet servers');
		$this->assertEquals(2,count($uploadToServletServersTask->getServers()));
		$this->assertTrue($uploadToServletServersTask instanceof EasyDeployWorkflows\Tasks\Common\CopyLocalFile);
		$this->assertEquals('/home/download/nasa/4711/somedownloadpackage.tar.gz', $uploadToServletServersTask->getFrom());
		$this->assertEquals('/tmp/', $uploadToServletServersTask->getTo());

		// last step deploys war local on 2 servers
		$servletTask = $workflow->getTaskByName('deploy the war file to the tomcat servers');
		$this->assertTrue($servletTask instanceof EasyDeployWorkflows\Tasks\Servlet\DeployWarInTomcat);
		$this->assertEquals(2,count($servletTask->getServers()));
		$this->assertEquals(8080,$servletTask->getTomcatPort());
		$this->assertEquals('foo',$servletTask->getTomcatUser());
		$this->assertEquals('/tmp/somedownloadpackage.tar.gz',$servletTask->getWarFileSourcePath());
	}

}