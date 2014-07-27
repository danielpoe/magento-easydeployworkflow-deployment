<?php

use EasyDeployWorkflows\Task as Task;

require_once EASYDEPLOY_WORKFLOW_ROOT . 'Classes/Autoloader.php';

class AbstractPartTest extends PHPUnit_Framework_TestCase {

	/**
	 *
	 * @test
	 * @return void
	 */
	public function canReplaceMarkers() {
		/** @var $part EasyDeployWorkflows\AbstractPart  */
		$part = $this->getMock('EasyDeployWorkflows\AbstractPart',array('nix'));

		$wfC= $this->getMock('EasyDeployWorkflows\Workflows\AbstractWorkflowConfiguration');
		$iC= $this->getMock('EasyDeployWorkflows\Workflows\InstanceConfiguration');
		$iC->expects($this->atLeastOnce())->method('getEnvironmentName')->will($this->returnValue('latest'));

		$this->assertEquals('latest',$iC->getEnvironmentName());
		$this->assertEquals('latest', $part->replaceConfigurationMarkers('###environment###',$wfC,$iC) );

		putenv('TEST=hallo');
		putenv('TEST2=du');
		$this->assertEquals('hallo', $part->replaceConfigurationMarkers('###ENV:TEST###',$wfC,$iC) );

		$this->assertEquals('hallo 1 du 2 latest', $part->replaceConfigurationMarkers('###ENV:TEST### 1 ###ENV:TEST2### 2 ###environment###',$wfC,$iC) );
	}

	

}