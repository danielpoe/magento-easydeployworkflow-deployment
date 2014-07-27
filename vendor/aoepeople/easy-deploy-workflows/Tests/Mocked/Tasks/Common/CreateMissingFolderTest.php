<?php

use EasyDeployWorkflows\Tasks as Tasks;

require_once EASYDEPLOY_WORKFLOW_ROOT . 'Classes/Autoloader.php';

class CreateMissingFolderTest extends AbstractMockedTest {
	/**
	 * @var \EasyDeployWorkflows\Tasks\Common\CreateMissingFolder
	 */
	protected $task;

	/**
	 * @var \EasyDeployWorkflows\Logger\Logger
	 */
	protected $loggerMock;

	/**
	 * test needs easydeploy to run
	 */
	public function setUp() {
		$this->requireEasyDeployClassesOrSkip();
		$this->task = new \EasyDeployWorkflows\Tasks\Common\CreateMissingFolder();

		$this->loggerMock = $this->getMock('\EasyDeployWorkflows\Logger\Logger',array(),array(),'',false);
		$this->task->injectLogger($this->loggerMock);

	}

	/**
	 *
	 * @test
	 * @return void
	 */
	public function canValidate() {

		$this->assertFalse($this->task->isValid());
		$this->task->setFolder('/folder');
		$this->assertTrue($this->task->isValid());
	}

	/**
	 *
	 * @test
	 * @return void
	 */
	public function canCreateFolder() {
		$serverMock	 = $this->getMock('EasyDeploy_RemoteServer',array('run','isDir'),array(),'',false);
		$this->task->addServer($serverMock);
		$this->task->setFolder('/folder');
		$taskRunInformation = new Tasks\TaskRunInformation();
		$serverMock->expects($this->at(0))->method('isDir')->will($this->returnValue(false));
		$serverMock->expects($this->at(1))->method('run')->with('mkdir -p /folder');
		$serverMock->expects($this->at(2))->method('run')->with('chmod g+rws /folder');
		$serverMock->expects($this->at(3))->method('isDir')->will($this->returnValue(true));
		$this->loggerMock->expects($this->at(2))->method('log')->with('Expected Folder is not present! Try to create "/folder"');
		$this->task->run($taskRunInformation);
	}

}