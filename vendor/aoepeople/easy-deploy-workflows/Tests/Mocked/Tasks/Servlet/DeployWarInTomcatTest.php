<?php

use EasyDeployWorkflows\Tasks as Tasks;
use EasyDeployWorkflows\Workflows as Workflows;

class DeployWarInTomcatTest extends AbstractMockedTest {

	/**
	 * @test
	 */
	public function canDeployWarFile() {
		$this->requireEasyDeployClassesOrSkip();

			//this dependency is only needed because other tasks require the web workflow
			/** @var $taskRunInformation  EasyDeployWorkflows\Tasks\TaskRunInformation */
		$taskRunInformation = new Tasks\TaskRunInformation();
		$loggerMock = $this->getMock('\EasyDeployWorkflows\Logger\Logger',array(),array(),'',false);


		$task = new Tasks\Servlet\DeployWarInTomcat();
		$task->injectLogger($loggerMock);
		$task->setTomcatUser('fred');
		$task->setTomcatPassword('feuerstein');
		$task->setTomcatPort(8090);
		$task->setTomcatPath('/Tracker');
		$task->setWarFileSourcePath('/tmp/tracker.war');


			/** @var $tomcatMock  EasyDeploy_RemoteServer */
		$tomcatMock	 = $this->getMock('EasyDeploy_RemoteServer',array(),array(),'',false);
		$tomcatMock->expects($this->any())->method('run')->will($this->returnCallback(
			function($command) {
				$isValidCommand = in_array(
					$command, array(
						'curl --upload-file /tmp/tracker.war -u fred:feuerstein "http://localhost:8090/manager/deploy?path=/Tracker&update=true"'
					)
				);

				if(!$isValidCommand) {
					$this->fail('Try to execute unexpected command during tomcat deployment '.$command);
				}
			}
		));


		$task->addServer($tomcatMock);
		$task->run($taskRunInformation);
	}
}