<?php

namespace EasyDeployWorkflows\Source\File;



/**
 * Download Source that abstracts from Jenkins and builds urls like:
 *
 * http://jenkins.aoemedia.de/job/aoemedia_build/lastSuccessfulBuild/artifact/
 *
 */
class JenkinsArtifactSource extends DownloadSource  {

	/**
	 * @var string
	 */
	protected $jenkinsBaseUrl;

	/**
	 * @var string
	 */
	protected $user;

	/**
	 * @var string
	 */
	protected $password;

	/**
	 * @var string
	 */
	protected $jobName;

	/**
	 * @var string
	 */
	protected $buildNr='lastSuccessfulBuild';

	/**
	 * @var string
	 */
	protected $artifactFileName;

	/**
	 * @var bool
	 */
	protected $downloadAllArtifactsZipped = false;


	protected function buildUrl() {
		$source = $this->jenkinsBaseUrl.'job/'.$this->jobName.'/'.$this->buildNr.'/artifact/';
		if ($this->downloadAllArtifactsZipped) {
			$source.='*zip*/archive.zip';
		}
		else {
			$source.=$this->artifactFileName;
		}
		if (isset($this->user) && isset($this->password)) {
			$auth = $this->user.':'.$this->password;
			$source = str_replace('://','://'.$auth.'@',$source);
		}
		return $source;
	}

	/**
	 * @param string $artifactName
     * @return self
	 */
	public function setArtifactFileName($artifactName) {
		$this->artifactFileName = $artifactName;
        return $this;
	}

	/**
	 * @return string
	 */
	public function getArtifactFileName() {
		return $this->artifactFileName;
	}

	/**
	 * @param string $buildNr
     * @return self
	 */
	public function setBuildNr($buildNr) {
		$this->buildNr = $buildNr;
        return $this;
	}

	/**
	 * @return string
	 */
	public function getBuildNr() {
		return $this->buildNr;
	}

	/**
	 * @param boolean $downloadAllArtifactsZipped
	 */
	public function setDownloadAllArtifactsZipped($downloadAllArtifactsZipped) {
		$this->downloadAllArtifactsZipped = $downloadAllArtifactsZipped;
	}

	/**
	 * @return boolean
	 */
	public function getDownloadAllArtifactsZipped() {
		return $this->downloadAllArtifactsZipped;
	}

	/**
	 * @param string $jobName
     * @return self
	 */
	public function setJobName($jobName) {
		$this->jobName = $jobName;
        return $this;
	}

	/**
	 * @return string
	 */
	public function getJobName() {
		return $this->jobName;
	}

	/**
	 * @param string $password
     * @return self
	 */
	public function setPassword($password) {
		$this->password = $password;
        return $this;
	}

	/**
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * @param string $user
     * @return self
	 */
	public function setUser($user) {
		$this->user = $user;
        return $this;
	}

	/**
	 * @return string
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @param string $jenkinsBaseUrl
     * @return self
	 */
	public function setJenkinsBaseUrl($jenkinsBaseUrl) {
		$this->jenkinsBaseUrl = rtrim($jenkinsBaseUrl,'/').'/';
        return $this;
	}

	/**
	 * @return string
	 */
	public function getJenkinsBaseUrl() {
		return $this->jenkinsBaseUrl;
	}

}
