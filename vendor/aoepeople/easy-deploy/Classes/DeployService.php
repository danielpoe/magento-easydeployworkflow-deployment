<?php
require_once(dirname(__FILE__).'/Exception/UnknownSourceFormatException.php');

/**
 * Common Deploy Service that can be used to deploy.
 * It first downloads the package and then installs the package using a given Install Strategie
 * @author Daniell Pötzinger
 */
class EasyDeploy_DeployService {

	/**
	 *
	 * @var $deliveryFolder string
	 */
	private $deliveryFolder;

	/**
	 * Environmentname for the installation (e.g. "production") This might be required by the install process to adjust environment specifc settings
	 * @var string
	 */
	private $environmentName;
	/**
	 * Target path for the installation
	 * @var string
	 */
	private $systemPath;
	/**
	 * Path to available backups, that might be required by the Install Strategie
	 * @var string
	 */
	private $backupstorageroot ;
	/**
	 * name of the group that should be used to fix permissions
	 * @var string
	 */
	private $deployerUnixGroup;

	/**
	 * @var EasyDeploy_InstallStrategy_Interface
	 */
	private $installStrategy;

	/**
	 * @var string
	 */
	private $projectName;

	/**
	 * @var string
	 */
	private $additionalInstallerParameters = '';

    /**
     * @var EasyDeploy_Helper_Downloader
     */
    private $downloader;

	/**
	 * @param EasyDeploy_InstallStrategy_Interface|null $installStrategy
     * @param EasyDeploy_Helper_Downloader $downloader
	 * @return EasyDeploy_DeployService
	 */
	public function __construct(EasyDeploy_InstallStrategy_Interface $installStrategy = NULL, EasyDeploy_Helper_Downloader $downloader = NULL) {
		if (is_null($installStrategy)) {
			$installStrategy = new EasyDeploy_InstallStrategy_PHPInstaller();
		}
        $this->setInstallStrategy($installStrategy);

        if (is_null($downloader)) {
            $downloader =  new EasyDeploy_Helper_Downloader();
        }
        $this->downloader = $downloader;
	}

	/**
	 * Deploys a package
	 * @param EasyDeploy_AbstractServer $server
	 * @param string $releaseName
	 * @param string $packageSourcePath
	 *
	 */
	public function deploy(EasyDeploy_AbstractServer $server, $releaseName, $packageSourcePath) {
		if (!$server->isDir($this->deliveryFolder)) {
			throw new Exception($this->deliveryFolder.' deliveryFolder not existend on server!');
		}

		$downloadedReleaseDirectory = $this->download($server, $packageSourcePath, $this->deliveryFolder . '/' . $releaseName);
		$this->installPackage($server, $downloadedReleaseDirectory);
	}

	/**
	 * Downloads the specified file (from) to the local directoy (to)
	 * $from can be a local file or a remote file (http:// and ssh:// supported)
	 *
	 * @param EasyDeploy_AbstractServer $server
	 * @param string $from
	 * @param string $to
	 * @return string	The path to the downloaded file
	 * @throws EasyDeploy_Exception_UnknownSourceFormatException
	 * @throws Exception
	 */
	public function download(EasyDeploy_AbstractServer $server, $from, $to) {
        return $this->downloader->download($server,$from,$to,$this->deployerUnixGroup);
	}

	/**
	 * Deploys to the given server
	 *
	 * @param EasyDeploy_AbstractServer $server
	 * @param string $packageDeliveryPath
	 * @throws Exception
	 */
	public function installPackage(EasyDeploy_AbstractServer $server, $packageDeliveryPath) {
		if (!isset($this->systemPath) || $this->systemPath == '') {
			throw new Exception('SystemPath not set');
		}

		if (!isset($this->environmentName) || $this->environmentName == '') {
			throw new Exception('Environment name not set');
		}

		// get package and copy to deliveryfolder
		$packageDeliveryFolder = pathinfo($packageDeliveryPath, PATHINFO_DIRNAME);

		// unzip package
		if (!$server->isFile($packageDeliveryPath)) {
			echo 'Try to detect Package by convention "Projectname-Releasename*".tar.gz.'.PHP_EOL;
			$_releaseVersion = basename($packageDeliveryFolder);
			$releasePackageName = $server->run('find ' . $packageDeliveryFolder . ' -type f -name "' . $this->projectName . '-' . $_releaseVersion . '*.tar.gz" | sort | tail -n 1', FALSE, TRUE);
			$releasePackageName = trim(basename($releasePackageName));
			$packageFileName=$this->projectName;
		} else {
			$releasePackageName = pathinfo($packageDeliveryPath, PATHINFO_BASENAME);        // get filename, results in something like "solrconf.tar.gz"
			$packageFileName=substr($releasePackageName,0,strpos($releasePackageName,'.')); //cuts file appendix, result in something like "solrconf"
		}

		if (!$server->isFile($packageDeliveryFolder .'/'.$releasePackageName)) {
			throw new Exception('Something went wrong! - I found no file to extract in "'.$packageDeliveryFolder .'/'.$releasePackageName.'"');
		}
		//extract
		$server->run('cd ' . $packageDeliveryFolder . '; tar -xzf ' . $releasePackageName);
		$this->installStrategy->installSteps($packageDeliveryFolder, $packageFileName, $this, $server);
		// delete unzipped folder
		$server->run('rm -rf ' . $packageDeliveryFolder . '/' . $packageFileName);
	}

	/**
	 * @return the $deliveryFolder
	 */
	public function getDeliveryFolder() {
		return $this->deliveryFolder;
	}

	/**
	 * @return the $environmentName
	 */
	public function getEnvironmentName() {
		return $this->environmentName;
	}

	/**
	 * @return the $systemPath
	 */
	public function getSystemPath() {
		return $this->systemPath;
	}

	/**
	 * @return the $backupstorageroot
	 */
	public function getBackupstorageroot() {
		return $this->backupstorageroot;
	}

	/**
	 * @param $deliveryFolder the $deliveryFolder to set
	 */
	public function setDeliveryFolder($deliveryFolder) {
		$this->deliveryFolder = $deliveryFolder;
	}

	/**
	 * @param $environmentName the $environmentName to set
	 */
	public function setEnvironmentName($environmentName) {
		$this->environmentName = $environmentName;
	}

	/**
	 * @param $systemPath the $systemPath to set
	 */
	public function setSystemPath($systemPath) {
		$this->systemPath = $systemPath;
	}

	/**
	 * @param $backupstorageroot the $backupstorageroot to set
	 */
	public function setBackupstorageroot($backupstorageroot) {
		$this->backupstorageroot = $backupstorageroot;
	}

	/**
	 * @return the $deployerUnixGroup
	 */
	public function getDeployerUnixGroup() {
		return $this->deployerUnixGroup;
	}

	/**
	 * @param $deployerUnixGroup the $deployerUnixGroup to set
	 */
	public function setDeployerUnixGroup($deployerUnixGroup) {
		$this->deployerUnixGroup = $deployerUnixGroup;
	}

	/**
	 * @return EasyDeploy_InstallStrategy_Interface
	 */
	public function getInstallStrategy() {
		return $this->installStrategy;
	}

	/**
	 * @param \EasyDeploy_InstallStrategy_Interface|\the $installStrategy $installStrategy to set
	 *
	 */
	public function setInstallStrategy(EasyDeploy_InstallStrategy_Interface $installStrategy) {
		$this->installStrategy = $installStrategy;
	}

	/**
	 * Default is set to true
	 *
	 * @deprecated this is a concept of the install strategy - you should pass a initialised strategie
	 *
	 * @param boolean $createBackup
	 * @return void
	 */
	public function setCreateBackupBeforeInstalling($createBackup) {
		echo EasyDeploy_Utils::formatMessage('setCreateBackupBeforeInstalling is deprecated - please use the method in the proper installstrategy',EasyDeploy_Utils::MESSAGE_TYPE_WARNING);
		$this->installStrategy->setCreateBackupBeforeInstalling($createBackup);
	}

	/**
	 * @param string $projectName
	 * @return void
	 */
	public function setProjectName($projectName) {
		$this->projectName = $projectName;
	}

	/**
	 * @return void
	 */
	public function getProjectName() {
		return $this->projectName;
	}

	/**
	 * @param string $additionalInstallerParameters
	 */
	public function setAdditionalInstallerParameters($additionalInstallerParameters) {
		$this->additionalInstallerParameters = $additionalInstallerParameters;
	}

	/**
	 * @return string
	 */
	public function getAdditionalInstallerParameters() {
		return $this->additionalInstallerParameters;
	}

}
