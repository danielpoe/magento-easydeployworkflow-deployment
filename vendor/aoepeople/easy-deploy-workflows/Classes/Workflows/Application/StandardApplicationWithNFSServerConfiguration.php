<?php

namespace EasyDeployWorkflows\Workflows\Application;

use EasyDeployWorkflows\Workflows as Workflows;
use EasyDeployWorkflows\Workflows\Exception as Exception;


/**
 * Configuration for the Basic Application Workflow
 *
 */
class StandardApplicationWithNFSServerConfiguration extends StandardApplicationConfiguration {

	/**
	 * @var string
	 */
	protected $syncFromNFSScript;

	/**
	 * @param string $syncFromNFSScript
	 */
	public function setSyncFromNFSScript($syncFromNFSScript) {
		$this->syncFromNFSScript = $syncFromNFSScript;
	}

	/**
	 * @return string
	 */
	public function getSyncFromNFSScript() {
		return $this->syncFromNFSScript;
	}

	/**
	 * @return string
	 */
	public function hasSyncFromNFSScript() {
		return !empty($this->syncFromNFSScript);
	}

	/**
	 * @param string $hostName
	 * @return NFSWebConfiguration
	 */
	public function setNFSServer($hostName) {
		$this->addServer($hostName,'nfs');
		return $this;
	}

	/**
	 * @return array
	 */
	public function getNfsServer() {
		$servers = $this->getServers('nfs');
		return $servers[0];
	}

	/**
	 * @return bool
	 */
	public function hasNFSServer() {
		return count($this->getServers('nfs')) == 1;
	}

	/**
	 * @return string
	 */
	public function getWorkflowClassName() {
		return 'EasyDeployWorkflows\Workflows\Application\StandardApplicationWithNFSServerWorkflow';
	}

	/**
	 * @return bool
	 */
	public function validate() {
		if(!$this->hasInstallServers() && $this->hasSyncFromNFSScript()) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException("Please configure at least one install server for workflow in order to be able to run the sync script ".get_class($this));
		}

		if(!$this->hasInstallationTargetFolder()) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException("Please configure the target folder for workflow: ".get_class($this));
		}

		if (!$this->hasSource()) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException("No download Source given: ".get_class($this));
		}

		if (!$this->hasNFSServer()) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException("No NFS Server given: ".get_class($this));
		}

		return true;
	}

}