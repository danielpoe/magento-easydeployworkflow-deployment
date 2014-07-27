<?php

namespace EasyDeployWorkflows;

use EasyDeployWorkflows\Logger\Logger;
use EasyDeployWorkflows\Workflows;
use EasyDeployWorkflows\Workflows\AbstractConfiguration;
use EasyDeployWorkflows\Workflows\InstanceConfiguration;

abstract class AbstractPart {

	/**
	 * @var Logger
	 */
	protected $logger;

	/**
	 * constructor
	 */
	public function __construct() {
		$this->injectLogger(Logger::getInstance());
	}

	/**
	 * @param Logger $logger
	 */
	public function injectLogger(Logger $logger) {
		$this->logger = $logger;
	}

	/**
	 * @param string $serverName
	 * @param string $serverKey
	 * @return \EasyDeploy_LocalServer|\EasyDeploy_RemoteServer
	 */
	protected function getServer($serverName, $serverKey = NULL) {
		if ($serverName == 'localhost') {
			$server = new \EasyDeploy_LocalServer($serverName);
		} else {
			$server = new \EasyDeploy_RemoteServer($serverName);
		}
		$server->setLogCommandsToScreen(FALSE);
		$server->setInternalTitle($serverName);
		if (!empty($serverKey)) {
			$server->setInternalTitle($serverKey);
		}
		return $server;
	}

	/**
	 * @param string $string
	 * @param AbstractConfiguration $workflowConfiguration
	 * @param InstanceConfiguration $instanceConfiguration
	 * @throws \Exception
	 * @return string
	 */
	public function replaceConfigurationMarkers($string, AbstractConfiguration $workflowConfiguration, InstanceConfiguration $instanceConfiguration) {
		$string = str_replace('###releaseversion###', $workflowConfiguration->getReleaseVersion(), $string);
		$string = str_replace('###environment###', $instanceConfiguration->getEnvironmentName(), $string);
		$string = str_replace('###environmentname###', $instanceConfiguration->getEnvironmentName(), $string);
		$string = str_replace('###projectname###', $instanceConfiguration->getProjectName(), $string);
		return $this->replaceWithEnvironmentVariables($string);
	}

	/**
	 * Replaces this pattern ###ENV:TEST### with the environment variable
	 *
	 * @param string $string
	 * @return string
	 * @throws \Exception
	 */
	protected function replaceWithEnvironmentVariables($string) {
		$matches = array();
		preg_match_all('/###ENV:([^#]*)###/', $string, $matches, PREG_PATTERN_ORDER);
		if (!is_array($matches) || !is_array($matches[0])) {
			return $string;
		}
		foreach ($matches[0] as $index => $completeMatch) {
			if (getenv($matches[1][$index]) == FALSE) {
				throw new \Exception('Expect an environment variable ' . $matches[1][$index]);
			}
			$string = str_replace($completeMatch, getenv($matches[1][$index]), $string);
		}
		return $string;
	}

	/**
	 * @param string $path
	 * @return mixed
	 */
	protected function getFilenameFromPath($path) {
		$dir = dirname($path) . DIRECTORY_SEPARATOR;
		return str_replace($dir, '', $path);
	}

}
