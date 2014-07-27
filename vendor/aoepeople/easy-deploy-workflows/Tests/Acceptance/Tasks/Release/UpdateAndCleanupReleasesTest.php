<?php

namespace EasyDeployWorkflows\Tests\Acceptance\Tasks\Release;



class UpdateAndCleanupReleasesTest extends \EasyDeployWorkflows\Tests\Acceptance\AbstractAcceptanceTest  {



	/**
	 * @test
	 */
	public function canDeployBasicApplicationToTargetFolder() {
		$localServer = new \EasyDeploy_LocalServer();
		$taskRunInformation = $this->getInitializedTaskRunInformation($localServer);

		//1 Add some release folders
		$localServer->run('mkdir '.$this->targetFolder.'/111');
		$localServer->run('mkdir '.$this->targetFolder.'/222');
		$localServer->run('mkdir '.$this->targetFolder.'/333');
		$localServer->run('mkdir '.$this->targetFolder.'/444');
		$localServer->run('mkdir '.$this->targetFolder.'/555');

		//2 create next symlink
		$updateNextTask = new \EasyDeployWorkflows\Tasks\Release\UpdateNext();
		$updateNextTask->setNextRelease('111');
		$updateNextTask->setReleasesBaseFolder($this->targetFolder);
		$updateNextTask->addServer($localServer);
		$updateNextTask->run($taskRunInformation);
		$this->assertTrue(is_link($this->targetFolder.'/next'),'Expected to have a valid link in '.$this->targetFolder.'/next');
		$this->assertEquals(readlink($this->targetFolder.'/next'),'111','Expected to have a valid link to 111');

		//3 Update current and previous
		$updateCurrentandPreviousTask = new \EasyDeployWorkflows\Tasks\Release\UpdateCurrentAndPrevious();
		$updateCurrentandPreviousTask->setReleasesBaseFolder($this->targetFolder);
		$updateCurrentandPreviousTask->addServer($localServer);
		$updateCurrentandPreviousTask->run($taskRunInformation);
		$this->assertTrue(is_link($this->targetFolder.'/current'),'Expected to have a valid link in '.$this->targetFolder.'/next');
		$this->assertEquals(readlink($this->targetFolder.'/current'),'111','Expected to have a valid link to 111');

		//4 update next again
		$updateNextTask->setNextRelease('222');
		$updateNextTask->run($taskRunInformation);
		$updateCurrentandPreviousTask->run($taskRunInformation);
		$this->assertEquals(readlink($this->targetFolder.'/current'),'222','Expected to have a valid link to 222 now');
	}




}