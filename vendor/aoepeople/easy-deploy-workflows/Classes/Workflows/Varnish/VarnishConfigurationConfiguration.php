<?php
namespace EasyDeployWorkflows\Workflows\Varnish;

use EasyDeployWorkflows\Workflows as Workflows;
use EasyDeployWorkflows\Workflows\Exception as Exception;

class VarnishConfigurationConfiguration extends Workflows\AbstractWorkflowConfiguration {

	/**
	 * @var array
	 */
	protected $directors = array();

	/**
	 * @var string
	 */
	protected $restartCommand;

	/**
	 * @var string
	 */
	protected $deployCommand;

	/**
	 * @var string
	 */
	protected $targetVarnishConfigurationFile;

	/**
	 * @param string $deployCommand
	 * @return VarnishConfigurationConfiguration
	 */
	public function setDeployCommand($deployCommand) {
		$this->deployCommand = $deployCommand;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDeployCommand() {
		return $this->deployCommand;
	}

	/**
	 * @param string $restartCommand
	 * @return VarnishConfigurationConfiguration
	 */
	public function setRestartCommand($restartCommand) {
		$this->restartCommand = $restartCommand;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRestartCommand() {
		return $this->restartCommand;
	}

	/**
	 * @param string $targetVarnishConfigurationFile
	 * @return VarnishConfigurationConfiguration
	 */
	public function setTargetVarnishConfigurationFile($targetVarnishConfigurationFile) {
		$this->targetVarnishConfigurationFile = $targetVarnishConfigurationFile;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTargetVarnishConfigurationFile() {
		return $this->targetVarnishConfigurationFile;
	}



	/**
	 * @param string $hostname
	 * @return VarnishConfigurationConfiguration
	 */
	public function addDirector(\EasyDeployWorkflows\Varnish\AbstractDirector $director) {
		$this->directors[] = $director;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getDirectors() {
		return $this->directors;
	}

	/**
	 * @param string $hostname
	 * @return VarnishConfigurationConfiguration
	 */
	public function addVarnishServer($hostName) {
		$this->addServer($hostName,'varnish');

		return $this;
	}



	/**
	 * @return bool
	 */
	public function hasVarnishServers() {
		return count($this->getVarnishServers()) > 0;
	}

	/**
	 * @return array
	 */
	public function getVarnishServers() {
		return $this->getServers('varnish');
	}

	/**
	 * @return boolean
	 */
	public function validate() {
		if(!$this->hasVarnishServers()) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('No Varnish Server configured');
		}

		if (empty($this->deploymentSource)) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('No deploymentSource configured');
		}

		if (empty($this->deployCommand) && empty($this->targetVarnishConfigurationFile)) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('Either a deployCommand or the targetVarnishConfigurationFile needs to be set! ');
		}

		return true;
	}

	/**
	 * @return string
	 */
	public function getWorkflowClassName() {
		return 'EasyDeployWorkflows\Workflows\Varnish\VarnishConfigurationWorkflow';
	}
}