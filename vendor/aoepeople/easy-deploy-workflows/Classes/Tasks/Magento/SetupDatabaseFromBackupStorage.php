<?php

namespace EasyDeployWorkflows\Tasks\Magento;

use EasyDeployWorkflows\Tasks;


/**
 * Depends on the Magento Backup Structure
 *
 */
class SetupDatabaseFromBackupStorage extends \EasyDeployWorkflows\Tasks\AbstractServerTask  {

	/**
	 * @var string
	 */
	protected $backupSourceFolder;

	/**
	 * @var string - e.g. 'Setup/GetDbSettings.sh'
	 */
	protected $detectDbSettingsScriptPath;

	protected $databaseImportScript = 'mgdeployscripts/import_dump_diffable.sh -u "###DB_USER###" -p "###DB_PASSWORD###" -h "###DB_HOST###" -d "###DB_NAME###" -s "###BACKUPSOURCEFOLDER###/db/latest" -t "###DB_TMP_DIR###"';

	protected $changeToFolder;

	protected $dbName;
	protected $dbHost;
	protected $dbUser;
	protected $dbPassword;
    protected $tmpDir;

	/**
	 * @var bool
	 */
	protected $skipIfTablesExist = true;



	/**
	 * @return boolean
	 * throws Exception\InvalidConfigurationException
	 */
	public function validate() {

		if (empty($this->backupSourceFolder)) {
			throw new \EasyDeployWorkflows\Exception\InvalidConfigurationException('backupSource Folder not set');
		}

		return true;
	}

	/**
	 * @param TaskRunInformation $taskRunInformation
	 * @return mixed
	 */
	protected function runOnServer(\EasyDeployWorkflows\Tasks\TaskRunInformation $taskRunInformation, \EasyDeploy_AbstractServer $server) {
		if (isset($this->detectDbSettingsScriptPath)) {
			$command ='source '.$this->detectDbSettingsScriptPath;
			$this->executeAndLog($server,$this->prependCommandWithChangeToFolder($command,$taskRunInformation));
			$this->dbName=getenv('DB_NAME');
			if ($this->dbName === false) {
				throw new \Exception('The script did not set the DB_HOST environment variable!');
			}
			$this->dbHost=getenv('DB_HOST');
			$this->dbUser=getenv('DB_USER');
			$this->dbPassword=getenv('DB_PASSWORD');
		}

		if (empty($this->dbName)) {
			throw new \Exception('No database host given - cannot setup from backupstorage');
		}

		/*
		 * todo
		 * if (!$this->databaseConnectionWorks()) {
			throw new \Exception('DB connection not working from server');
		}
		 */


		$host = $this->replaceConfigurationMarkersWithTaskRunInformation($this->dbHost,$taskRunInformation);
		$dbName = $this->replaceConfigurationMarkersWithTaskRunInformation($this->dbName,$taskRunInformation);
		$dbUser = $this->replaceConfigurationMarkersWithTaskRunInformation($this->dbUser,$taskRunInformation);
		$dbPassword = $this->replaceConfigurationMarkersWithTaskRunInformation($this->dbPassword,$taskRunInformation);
		$tmpDir = $this->replaceConfigurationMarkersWithTaskRunInformation($this->tmpDir, $taskRunInformation);
		if ($this->skipIfTablesExist && $this->databaseHasTables($server,$host,$dbName,$dbUser,$dbPassword)) {
			$this->logger->log('The database already has tables - skipping import!',\EasyDeployWorkflows\Logger\Logger::MESSAGE_TYPE_WARNING);
			return;
		}
		$importCommand = $this->replaceConfigurationMarkersWithTaskRunInformation($this->databaseImportScript,$taskRunInformation);
		$importCommand = str_replace('###DB_HOST###',$host,$importCommand);
		$importCommand = str_replace('###DB_NAME###',$dbName,$importCommand);
		$importCommand = str_replace('###DB_USER###',$dbUser,$importCommand);
		$importCommand = str_replace('###DB_PASSWORD###',$dbPassword,$importCommand);
		$importCommand = str_replace('###DB_TMP_DIR###', $tmpDir, $importCommand);
		$importCommand = str_replace('###BACKUPSOURCEFOLDER###',$this->replaceConfigurationMarkersWithTaskRunInformation($this->backupSourceFolder,$taskRunInformation),$importCommand);

		$this->executeAndLog($server,$this->prependCommandWithChangeToFolder($importCommand,$taskRunInformation));
	}

	/**
	 * @param $server
	 * @param $host
	 * @param $dbName
	 * @param $dbUser
	 * @param $dbPassword
	 * @return bool
	 */
	protected function databaseHasTables($server,$host,$dbName,$dbUser,$dbPassword) {
		try {
			$tables = $server->run('mysql -u '.$dbUser.' -p'.$dbPassword.' -h '.$host.' '.$dbName.' -e "show tables"',FALSE,TRUE);
			if (strpos($tables,'core_config_data') !==false) {
				return true;
			}
			else {
				return false;
			}
		}
		catch (\EasyDeploy_Exception_CommandFailedException $e) {
			return false;
		}
	}


	/**
	 * @param $command
	 * @return string
	 */
	protected function prependCommandWithChangeToFolder($command,$taskRunInformation) {
		if (isset($this->changeToFolder)) {
			$command ='cd '.$this->replaceConfigurationMarkersWithTaskRunInformation($this->changeToFolder,$taskRunInformation).'; '.$command;
		}
		return $command;
	}

	/**
	 * @param string $backupSourceFolder
	 * @return self
	 */
	public function setBackupSourceFolder($backupSourceFolder) {
		$this->backupSourceFolder = $backupSourceFolder;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getBackupSourceFolder() {
		return $this->backupSourceFolder;
	}

	/**
	 * @param $changeToFolder
	 * @return SetupDatabaseFromBackupStorage
	 */
	public function setChangeToFolder($changeToFolder) {
		$this->changeToFolder = $changeToFolder;
		return $this;
	}

	public function getChangeToFolder() {
		return $this->changeToFolder;
	}

	/**
	 * @param $databaseImportScript
	 * @return SetupDatabaseFromBackupStorage
	 */
	public function setDatabaseImportScript($databaseImportScript) {
		$this->databaseImportScript = $databaseImportScript;
		return $this;
	}

	public function getDatabaseImportScript() {
		return $this->databaseImportScript;
	}

	/**
	 * @param $dbHost
	 * @return SetupDatabaseFromBackupStorage
	 */
	public function setDbHost($dbHost) {
		$this->dbHost = $dbHost;
		return $this;
	}

	public function getDbHost() {
		return $this->dbHost;
	}

	/**
	 * @param $dbName
	 * @return SetupDatabaseFromBackupStorage
	 */
	public function setDbName($dbName) {
		$this->dbName = $dbName;
		return $this;
	}

	public function getDbName() {
		return $this->dbName;
	}

	/**
	 * @param $dbPassword
	 * @return self
	 */
	public function setDbPassword($dbPassword) {
		$this->dbPassword = $dbPassword;
		return $this;
	}

	public function getDbPassword() {
		return $this->dbPassword;
	}

	/**
	 * @param $dbUser
	 * @return SetupDatabaseFromBackupStorage
	 */
	public function setDbUser($dbUser) {
		$this->dbUser = $dbUser;
		return $this;
	}

	public function getDbUser() {
		return $this->dbUser;
	}

    /**
     * @param $tmpDir
     * @return SetupDatabaseFromBackupStorage
     */
    public function setTmpDir($tmpDir) {
        $this->tmpDir = $tmpDir;
        return $this;
    }

    public function getTmpDir() {
        return $this->tmpDir;
    }

	public function setDetectDbSettingsScriptPath($detectDbSettingsScriptPath) {
		$this->detectDbSettingsScriptPath = $detectDbSettingsScriptPath;
	}

	public function getDetectDbSettingsScriptPath() {
		return $this->detectDbSettingsScriptPath;
	}


}