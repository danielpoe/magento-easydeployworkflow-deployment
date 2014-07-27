<?php

namespace EasyDeployWorkflows\Source\Folder;

/**
 * Git Checkout
 */
class GitCloneSource implements FolderSourceInterface  {

	/**
	 * @var string
	 */
	protected $repository;

	/**
	 * @var string
	 */
	protected $tag;

	/**
	 * @var string
	 */
	protected $individualTargetFolderName;

	/**
	 * For folder sources: Some sources can directly sync to a target folder with a custom name
	 * @param $name string
	 * @return self
	 */
	public function setIndividualTargetFolderName($name) {
		$this->individualTargetFolderName = $name;
	}

	/**
	 * @param string $parentFolder
	 * @return string
	 */
	public function getDownloadCommand($parentFolder) {
		$command = 'cd '.$parentFolder.'; ';
		$options = '--recursive ';
		if (isset($this->tag)) {
			$options .='-b '.$this->tag.' ';
		}
		$command .= 'GIT_SSL_NO_VERIFY=1 git clone '.$options.$this->repository.' '.$this->individualTargetFolderName;
		return trim($command);
	}

	/**
	 * @param string $repository
	 * @return $this
	 */
	public function setRepository($repository) {
		$this->repository = $repository;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRepository() {
		return $this->repository;
	}

	/**
	 * @param string $tag
	 */
	public function setTag($tag) {
		$this->tag = $tag;
	}

	/**
	 * @return string
	 */
	public function getTag() {
		return $this->tag;
	}

	/**
	 * @return string
	 */
	public function getShortExplain() {
		return 'Checkout from: '.$this->repository.' Tag:'.$this->tag;
	}

	/**
	 * @return string
	 */
	public function getFolderName() {
		if (isset($this->individualTargetFolderName)) {
			return $this->individualTargetFolderName;
		}
		//defaults to name of repository (last path part)
		$lastPath = substr($this->repository,strrpos($this->repository,'/')+1);
		return substr($lastPath,0,strpos($lastPath,'.'));
	}

}
