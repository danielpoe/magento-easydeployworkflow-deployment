<?php

namespace EasyDeployWorkflows\Workflows;

use EasyDeployWorkflows\Exception\InvalidConfigurationException;
use EasyDeployWorkflows\ValidateableInterface;
use EasyDeployWorkflows\Workflows;
use EasyDeployWorkflows\Workflows\Exception as Exception;

require_once dirname(__FILE__) . '/../ValidateableInterface.php';


abstract class AbstractConfiguration implements ValidateableInterface {

	/**
	 * @var
	 */
	private $folders = array();

	/**
	 * @var
	 */
	protected $servers = array();

	/**
	 * @var string a speaking title
	 */
	protected $title;

	/**
	 * @param string $scope
	 * @param int $index
	 * @return string
	 */
	protected function getFolder($scope, $index = 0) {
		if (!isset($this->folders[$scope][$index])) {
			return '';
		}

		return $this->folders[$scope][$index];
	}

	/**
	 * @param string $scope
	 * @return array
	 */
	protected function getFolders($scope) {
		if (!isset($this->folders[$scope])) {
			return array();
		}

		return $this->folders[$scope];
	}

	/**
	 * @param string $folderName
	 * @param string $scope
	 * @param int $index
	 * @return $this
	 * @throws \InvalidArgumentException
	 */
	protected function setFolder($folderName, $scope, $index = 0) {
		if (!is_int($index)) {
			throw new \InvalidArgumentException('Invalid index ' . serialize($index));
		}
		if (!is_string($scope)) {
			throw new \InvalidArgumentException('Invalid scope ' . serialize($scope));
		}
		if (!is_string($folderName)) {
			throw new \InvalidArgumentException('Invalid folder ' . serialize($folderName));
		}
		$folderName = rtrim($folderName, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$this->folders[$scope][$index] = $folderName;

		return $this;
	}

	/**
	 * @param string $scope
	 * @param int $index
	 * @return $this
	 * @throws \InvalidArgumentException
	 */
	protected function unsetFolder($scope, $index = 0) {
		if (!is_int($index)) {
			throw new \InvalidArgumentException('Invalid index ' . serialize($index));
		}
		if (!is_string($scope)) {
			throw new \InvalidArgumentException('Invalid scope ' . serialize($scope));
		}
		unset($this->folders[$scope][$index]);

		return $this;
	}

	/**
	 * @param string $folderName
	 * @param string $scope
	 * @return $this
	 * @throws \InvalidArgumentException
	 */
	protected function addFolder($folderName, $scope) {
		if (!is_string($scope)) {
			throw new \InvalidArgumentException('Invalid scope ' . serialize($scope));
		}
		if (!is_string($folderName)) {
			throw new \InvalidArgumentException('Invalid folder ' . serialize($folderName));
		}

		$this->folders[$scope][] = $folderName;

		return $this;
	}

	/**
	 * @param string $hostName
	 * @param string $scope
	 * @return $this
	 * @throws \InvalidArgumentException
	 */
	protected function addServer($hostName, $scope) {
		if (!is_string($scope)) {
			throw new \InvalidArgumentException('Invalid scope ' . serialize($scope));
		}

		if (!is_string($hostName)) {
			throw new \InvalidArgumentException('Invalid hostname ' . serialize($hostName));
		}

		$hasHost = isset($this->servers[$scope]) && is_array($this->servers[$scope]);
		if ($hasHost && in_array($hostName, $this->servers[$scope])) {
			throw new \InvalidArgumentException('Could not set same hostname twice');
		}

		$this->servers[$scope][$hostName] = $hostName;

		return $this;
	}

	/**
	 * @param $scope
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	protected function getServers($scope) {
		if (!is_string($scope)) {
			throw new \InvalidArgumentException('Invalid scope ' . serialize($scope));
		}

		if (!isset($this->servers[$scope]) || !is_array($this->servers[$scope])) {
			return array();
		}

		return $this->servers[$scope];
	}

	/**
	 * @return boolean
	 */
	public function isValid() {
		try {
			$this->validate();
		} catch (InvalidConfigurationException $e) {
			return false;
		}

		return true;
	}

	/**
	 * @param string $title
	 * @return $this
	 */
	public function setTitle($title) {
		$this->title = $title;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return boolean
	 * throws Exception\InvalidConfigurationException
	 */
	public function validate() {
		return false;
	}

}
