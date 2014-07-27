<?php

namespace EasyDeployWorkflows\Varnish;

class Probe {

	/**
	 * @var string
	 */
	private $url = '/';
	/**
	 * @var string
	 */
	private $interval = '5s';
	/**
	 * @var string
	 */
	private $timeout = '1s';
	/**
	 * @var int
	 */
	private $window = 5;
	/**
	 * @var int
	 */
	private $threshold = 3;

	/**
	 * @param string $interval
	 */
	public function setInterval($interval) {
		$this->interval = $interval;
	}

	/**
	 * @return string
	 */
	public function getInterval() {
		return $this->interval;
	}

	/**
	 * @param int $threshold
	 */
	public function setThreshold($threshold) {
		$this->threshold = $threshold;
	}

	/**
	 * @return int
	 */
	public function getThreshold() {
		return $this->threshold;
	}

	/**
	 * @param string $timeout
	 */
	public function setTimeout($timeout) {
		$this->timeout = $timeout;
	}

	/**
	 * @return string
	 */
	public function getTimeout() {
		return $this->timeout;
	}

	/**
	 * @param string $url
	 */
	public function setUrl($url) {
		$this->url = $url;
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @param int $window
	 */
	public function setWindow($window) {
		$this->window = $window;
	}

	/**
	 * @return int
	 */
	public function getWindow() {
		return $this->window;
	}



}