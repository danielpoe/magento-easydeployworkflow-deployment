<?php

namespace EasyDeployWorkflows\Varnish;

class RoundRobinDirector extends AbstractDirector {
	/**
	 * @var string
	 */
	protected $varnishDirectorConfigName = 'round-robin';

	public function addBackend(Backend $backend) {
		$this->backendDatas[] = $backend;
	}

	/**
	 * @param Backend $backend
	 * @return string
	 */
	protected function getBackendCode($backend) {
		return '.backend = ' . $backend->generateCode();
	}
}