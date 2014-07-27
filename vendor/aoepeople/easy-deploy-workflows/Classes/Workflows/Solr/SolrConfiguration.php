<?php

namespace EasyDeployWorkflows\Workflows\Solr;

use EasyDeployWorkflows\Workflows as Workflows;
use EasyDeployWorkflows\Workflows\Exception as Exception;

class SolrConfiguration extends Workflows\AbstractWorkflowConfiguration {

	/**
	 * @var string
	 */
	protected $instancePath = '';

	/**
	 * @var string
	 */
	protected $restartCommand = '';

	/**
	 * @var string
	 * The folder where the solr package may be uploaded before it is installed - can for example be a tmp folder
	 */
	protected $tempDeliverFolder = '';

	/**
	 * @param string $tempDeliverFolder
	 */
	public function setTempDeliverFolder($tempDeliverFolder)
	{
		$this->tempDeliverFolder = $tempDeliverFolder;
	}

	/**
	 * @return string
	 */
	public function getTempDeliverFolder()
	{
		return $this->tempDeliverFolder;
	}

	/**
	 * @return string
	 */
	public function hasTempDeliverFolder()
	{
		return !empty($this->tempDeliverFolder);
	}

	/**
	 * @param string $instancePath
	 * @return SolrConfiguration
	 */
	public function setInstancePath($instancePath) {
		$this->instancePath = $instancePath;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getInstancePath() {
		return $this->instancePath;
	}

	/**
	 * @param string $restartCommand
	 * @return SolrConfiguration
	 */
	public function setRestartCommand($restartCommand) {
		$this->restartCommand = $restartCommand;

		return $this;
	}

	/**
	 * @param $hostName
	 * @return SolrConfiguration
	 */
	public function addMasterServers($hostName) {
		$this->addServer($hostName, 'solrmaster');

		return $this;
	}

	/**
	 * @param $hostName
	 * @return SolrConfiguration
	 */
	public function getMasterServers() {
		return $this->getServers('solrmaster');
	}

	/**
	 * @return string
	 */
	public function getRestartCommand() {
		return $this->restartCommand;
	}

	/**
	 * @return boolean
	 */
	public function validate() {
		if(trim($this->restartCommand) == '') {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('Please configure a start command for the solr configuration!');
		}

		if(trim($this->instancePath) == '') {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('Please configure an instance path for the solr configuration!');
		}

		return true;
	}

	/**
	 * @return string
	 */
	public function getWorkflowClassName() {
		return 'EasyDeployWorkflows\Workflows\Solr\SolrWorkflow';
	}
}