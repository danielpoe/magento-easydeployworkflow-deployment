<?php

namespace EasyDeployWorkflows\Source;

/**
 * Common Source Interface
 */
interface SourceInterface  {

	/**
	 * Downloads the given source on the given server in the given parent path
	 *
	 * @param string $parentFolder
	 * @return string
	 */
	public function getDownloadCommand($parentFolder);

	/**
	 * For usage in logs
	 *
	 * @return string
	 */
	public function getShortExplain();

}
