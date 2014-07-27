<?php


require_once EASYDEPLOY_WORKFLOW_ROOT . 'Classes/Autoloader.php';

class GitCloneSourceTest extends AbstractMockedTest {
	/**
	 * @var \EasyDeployWorkflows\Source\Folder\GitCloneSource
	 */
	protected $source;


	/**
	 * test needs easydeploy to run
	 */
	public function setUp() {
		$this->source = new \EasyDeployWorkflows\Source\Folder\GitCloneSource();
	}

	/**
	 *
	 * @test
	 * @return void
	 */
	public function canCreateCorrectCommand() {
		$this->source->setRepository('git://github.com/AOEmedia/EasyDeployWorkflows.git');
		$this->assertEquals('cd /; GIT_SSL_NO_VERIFY=1 git clone --recursive git://github.com/AOEmedia/EasyDeployWorkflows.git',$this->source->getDownloadCommand('/'));
		$this->assertEquals('EasyDeployWorkflows',$this->source->getFolderName());

		$this->source->setTag('tag');
		$this->source->setIndividualTargetFolderName('version1');
		$this->assertEquals('cd /; GIT_SSL_NO_VERIFY=1 git clone --recursive -b tag git://github.com/AOEmedia/EasyDeployWorkflows.git version1',$this->source->getDownloadCommand('/'));

	}


}