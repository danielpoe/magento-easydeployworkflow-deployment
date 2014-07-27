<?php

namespace EasyDeployWorkflows\Source\Folder;


/**
 * Folder Source Interface
 */
interface FolderSourceInterface extends \EasyDeployWorkflows\Source\SourceInterface {


	/**
	 * For folder sources: Some sources can directly sync to a target folder with a custom name
	 * @param $name string
	 * @return self
	 */
	public function setIndividualTargetFolderName($name);

	/**
	 * @return string
	 */
	public function getFolderName();

}
