<?php

use EasyDeployWorkflows\Workflows;

abstract class AbstractMockedTest extends PHPUnit_Framework_TestCase {

	protected function requireEasyDeployClassesOrSkip() {
		if (is_file(EASYDEPLOY_WORKFLOW_ROOT.'../EasyDeploy/Classes/RemoteServer.php')) {
			require_once EASYDEPLOY_WORKFLOW_ROOT.'../EasyDeploy/Classes/RemoteServer.php';
			require_once EASYDEPLOY_WORKFLOW_ROOT.'../EasyDeploy/Classes/LocalServer.php';
			require_once EASYDEPLOY_WORKFLOW_ROOT.'../EasyDeploy/Classes/Helper/Downloader.php';
		}

		if (!class_exists('EasyDeploy_RemoteServer')) {
			$this->markTestSkipped(
				'EasyDeploy classes not available.'
			);
		}
	}

}

?>