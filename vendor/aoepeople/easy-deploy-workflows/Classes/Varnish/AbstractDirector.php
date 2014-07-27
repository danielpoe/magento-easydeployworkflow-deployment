<?php

namespace EasyDeployWorkflows\Varnish;

abstract class AbstractDirector {


	/**
	 * @var string
	 */
	private $name = 'default';

	/**
	 * @var array
	 */
	protected $backendDatas = array();

	/**
	 * @var string
	 */
	protected $varnishDirectorConfigName = '!abstract!';

	/**
	 * @return string
	 */
	public function generateCode() {
		$code = 'director '.$this->name.' '.$this->varnishDirectorConfigName.' {'.PHP_EOL;
		foreach ($this->backendDatas as $backendData) {
			$code .= '	{ '.$this->getBackendCode($backendData).' }'.PHP_EOL;
		}
		$code .= '}'.PHP_EOL;
		return $code;
	}

	abstract protected function getBackendCode( $backendData );


	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
}