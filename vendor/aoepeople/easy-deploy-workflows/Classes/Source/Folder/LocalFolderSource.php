<?php

namespace EasyDeployWorkflows\Source\Folder;



/**
 *  Source that uses a local file
 *  (also used for acceptance tests with local fixtures)
 *
 */
class LocalFolderSource implements FolderSourceInterface  {

	/**
	 * @var string
	 */
	protected $source;

	/**
	 * @var string
	 */
	protected $individualTargetFolderName;

	public function __construct($source = '') {
		$this->setSource($source);
	}

	/**
	 * For folder sources: Some sources can directly sync to a target folder with a custom name
	 * @param $name string
	 * @return self
	 */
	public function setIndividualTargetFolderName($name) {
		$this->individualTargetFolderName = $name;
	}



	/**
	 * Downloads the given source on the given server in the given parent path
	 *
	 * @return void
	 */
	public function getDownloadCommand($parentFolder) {
		$parentFolder = rtrim($parentFolder,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
		$command = 'cp  -R '.$this->source.' '.$parentFolder.$this->getFolderName();
		return $command;
	}



	/**
	 * @param string $source
	 */
	public function setSource($source) {
		$this->source = $source;
	}



	/**
	 * @return string
	 */
	public function getShortExplain() {
		return 'Copy recursive from:'.$this->source;
	}

	/**
	 * @return string
	 */
	public function getFolderName() {
		if (isset($this->individualTargetFolderName)) {
			return $this->individualTargetFolderName;
		}
		return $this->getFilenameFromPath($this->source);
	}

	/**
	 * @param $path
	 * @return string
	 */
	protected function getFilenameFromPath($path) {
		$dir = dirname($path).DIRECTORY_SEPARATOR;
		return str_replace($dir,'',$path);
	}
}
