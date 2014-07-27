<?php
/**
 * Copyright notice
 *
 * (c) 2011 AOE media GmbH <dev@aoemedia.de>
 * All rights reserved
 *
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */


/**
 * Rollback Service that rolls back by switching symlink
 * 
 * @author: Michael Klapper <michael.klapper@aoemedia.de>
 * @date: 28.10.11
 * @time: 15:41
 */
class EasyDeploy_RollbackService {

	/**
	 * Environment can be "staging" or "production"
	 * @var string
	 */
	private $environment;

	/**
	 * Target path for the installation
	 * @var string
	 */
	private $systemPath;

	/**
	 * @var EasyDeploy_AbstractServer
	 */
	protected $server;

	/**
	 * @param string $environment
	 * @return void
	 */
	public function setEnvironment($environment) {
		$this->environment = $environment;
	}

	/**
	 * This is not the path to web root!
	 *
	 * @param string $systemPath
	 * @return void
	 */
	public function setSystemPath($systemPath) {
		$this->systemPath = $systemPath;
	}

	/**
	 * Switch active with inactive environment.
	 *
	 * @param \EasyDeploy_AbstractServer $server
	 * @return void
	 */
	public function process(EasyDeploy_AbstractServer $server) {
		$environmentService = new EasyDeploy_Environment($server, $this->environment, $this->systemPath);
		$tmpLinkName = $this->environment . '_tmp';
		$target = $environmentService->getInactiveEnvironment();
		
		$command =<<<EOD
cd $this->systemPath \
&& ln -s $target $tmpLinkName  \
&& mv -Tf $tmpLinkName $this->environment
EOD;

		$server->run($command);
	}
}
