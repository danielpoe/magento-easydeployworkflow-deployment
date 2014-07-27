<?php

namespace EasyDeployWorkflows\Varnish;

class RandomDirector extends AbstractDirector {

	/**
	 * @var string
	 */
	protected $varnishDirectorConfigName = 'random';

	/**
	 * @param Backend $backend
	 * @param int $weight
	 */
	public function addBackend(Backend $backend, $weight = 1) {
		$this->backendDatas[] = array( $backend, $weight);
	}

	/**
	 * @param Backend $backend
	 * @return string
	 */
	protected function getBackendCode($backendsData) {
		return '  .backend = { ' . $backendsData[0]->generateCode().' } .weight = '.$backendsData[1].'; ';
	}

}
