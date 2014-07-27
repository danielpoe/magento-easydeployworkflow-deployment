<?php

namespace EasyDeployWorkflows\Tasks\Common;

use EasyDeployWorkflows\Tasks;



class AppendContent extends \EasyDeployWorkflows\Tasks\AbstractServerTask  {

	/**
	 * @var string
	 */
	protected $content = '';

	/**
	 * @var string
	 */
	protected $file;

	/**
	 * @param string $content
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @param string $file
	 */
	public function setFile($file) {
		$this->file = $file;
	}

	/**
	 * @return string
	 */
	public function getFile() {
		return $this->file;
	}



	/**
	 * @param TaskRunInformation $taskRunInformation
	 * @return mixed
	 */
	protected function runOnServer(\EasyDeployWorkflows\Tasks\TaskRunInformation $taskRunInformation,\EasyDeploy_AbstractServer $server) {
		if (!$server->isFile($this->getFile())) {
			throw new \Exception($this->getFile().' is no file!');
		}
		$command = 'cat >> '.$this->getFile().' <<EOF'.PHP_EOL;
		$command .= $this->content;
		$command .=PHP_EOL.'EOF';

		$this->logger->log('Appending content to '.$this->getFile());
		$server->run($command);

	}

	/**
	 * @return boolean
	 * throws Exception\InvalidConfigurationException
	 */
	public function validate() {
		if (!isset($this->file)) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('file not set');
		}

		return true;
	}
}