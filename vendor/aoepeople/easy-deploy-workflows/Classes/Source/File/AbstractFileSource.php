<?php

namespace EasyDeployWorkflows\Source\File;

/**
 * Common function of file sources
 */
abstract class AbstractFileSource  {

	/**
	 * @var string
	 */
	protected $folderNameInArchive;

	/**
	 * Defaults to name of file
	 * @return string
	 */
	public function getFolderNameInArchive() {
		if (!isset($this->folderNameInArchive)) {
			return $this->getFileNameWithOutExtension();
		}
		return $this->folderNameInArchive;
	}

	/**
	 * @param $name
	 */
	public function setFolderNameInArchive($name) {
		$this->folderNameInArchive = $name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFileNameWithOutExtension() {
		return substr($this->getFileName(),0,strpos($this->getFileName(),'.'));
	}

	abstract public function getFileName();
}
