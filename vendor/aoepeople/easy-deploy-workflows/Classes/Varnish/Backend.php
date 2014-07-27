<?php

namespace EasyDeployWorkflows\Varnish;

class Backend {

	/**
	 * @var Probe
	 */
	private $probe;

	/**
	 * @var string
	 */
	private $host;

	/**
	 * @var int
	 */
	private $port = 'http';

	/**
	 * @var string
	 */
	private $connectTimeout;

	/**
	 * @var string
	 */
	private $firstByteTimeout;

	/**
	 * @var string
	 */
	private $betweenBytesTimeout;

	/**
	 * @param \EasyDeployWorkflows\Varnish\Probe $probe
	 */
	public function setProbe(\EasyDeployWorkflows\Varnish\Probe $probe) {
		$this->probe = $probe;
	}

	/**
	 * @return \EasyDeployWorkflows\Varnish\Probe
	 */
	public function getProbe() {
		return $this->probe;
	}

	/**
	 * @param string $host
	 */
	public function setHost($host) {
		$this->host = $host;
	}

	/**
	 * @return string
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 * @param int $port
	 */
	public function setPort($port) {
		$this->port = $port;
	}

	/**
	 * @return int
	 */
	public function getPort() {
		return $this->port;
	}

	/**
	 * @param string $betweenBytesTimeout
	 */
	public function setBetweenBytesTimeout($betweenBytesTimeout) {
		$this->betweenBytesTimeout = $betweenBytesTimeout;
	}

	/**
	 * @return string
	 */
	public function getBetweenBytesTimeout() {
		return $this->betweenBytesTimeout;
	}

	/**
	 * @param string $connectTimeout
	 */
	public function setConnectTimeout($connectTimeout) {
		$this->connectTimeout = $connectTimeout;
	}

	/**
	 * @return string
	 */
	public function getConnectTimeout() {
		return $this->connectTimeout;
	}

	/**
	 * @param string $firstByteTimeout
	 */
	public function setFirstByteTimeout($firstByteTimeout) {
		$this->firstByteTimeout = $firstByteTimeout;
	}

	/**
	 * @return string
	 */
	public function getFirstByteTimeout() {
		return $this->firstByteTimeout;
	}



	public function generateCode() {
		$code = '.host = "'.$this->getHost().'"; ';
		$code .= '.port = "'.$this->getPort().'"; ';
		if (!empty($this->probe)) {
			$code .= '.probe = "'.$this->probe.'"; ';
		}
		if (!empty($this->firstByteTimeout)) {
			$code .= '.first_byte_timeout = '.$this->firstByteTimeout.'; ';
		}
		if (!empty($this->connectTimeout)) {
			$code .= '.connect_timeout = '.$this->connectTimeout.'; ';
		}
		if (!empty($this->betweenBytesTimeout)) {
			$code .= '.between_bytes_timeout = '.$this->betweenBytesTimeout.'; ';
		}

		return $code;
	}

}