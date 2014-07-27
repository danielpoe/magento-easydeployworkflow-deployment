What is EasyDeployWorkflows?
=====================

EasyDeployWorkflows offers a concept to develop Deployment Workflows on Top of EasyDeploy

Build status: |buildStatusIcon|

Concept
-------------
Each component of your Application is deployed using a Workflow.
A workflow needs 3 Objects to run:

* A Workflow Configuration (Specific Configuration Object for that Workflow, Including the releaseVersion.)
* A InstanceConfiguration (Holding common Data about the name of the environment and the project.)

The idea ist, that the Workflows can be reused for deploying different projects to different environments.
Managing the differences between the environments comes down to manage the WorkflowConfiguration.

The typical Responsibility for a Workflow is:

* Get the Application from a given source (e.g. Download the Artifact that should be installed from Jenkins)
* Install the Application on the infrastructure - that means there might be multiple servers involved

Features
-----------------
List of Features:

* Reusable deployment abstraction (based on configurable workflows and tasks)
* Logging (details go in a logfile, important stuff go to STDOUT)
* Tested (most part of the logic is unit tested - of course this tool is new and there is no warranty for anything)
* "Best practice" Tasks and Workflows that can be used out of the box:

  * Dealing with Symlinks and a Releasefolder structure
  * Cleanup of old releases and/or deliveries

* Source abstraction (Use git,svn or (zipped) jenkins artifacts)
* "dryRun" flag - which does nothing but only logs what commands on which server it would have done
* Varnish: Classes and Workflows to generate Varnish backend directors

Motivation - or how it can be used
-----------------
Lets say you have a complex Application, that itself consists of multiple (sub)applications.
Like for example a search-application and a cms-application.

You want to deploy that application as smooth as possible with simple commands.
This will deploy and configure the two applications on our local virtualbox for example:
::
	git clone --recursive <yourdeploymentscriptrepository>
	php deployment/deploy.php --environment=devbox

This will deploy and configure the same applications on our physical cluster with 10 nodes:
::
	php deployment/deploy.php --environment=production

This will deploy and configure the same applications on our aws cloud instances - during selfprovising:
::
	# search layer ec2 instances may run
	php deployment/deploy.php --environment=aws --subapplication=search
	# search layer ec2 instances may run
	php deployment/deploy.php --environment=aws --subapplication=cms




Workflow and Task
-----------------
A Workflow can use several Tasks to do what its supposed to do.
There is the "AbstractTaskBasedWorkflow" Class as a Basis for Task based Workflows.

To build our own Workflow you probably want to extend one of the existing task based Workflows and modify or extend the Tasks it uses.

WorkflowConfiguration and InfrastructureConfiguration
------------------------

* The Workflow Configuration represents the data that is required by the Deployment Workflow.
* It describes the target server Infrastructure of the deployment. Therefore it is the part of your Deployment, that typically is environment specific.
* Normally you have versions of the Configuration for each Environment (devbox, staging, production, amazon). See below for the suggested folder structure.


Sources
----------------
Most of the Workflows start with getting your application from a Source.
A Source can either be a file or a folder.
If its a file most workflows expect this to be a archive. An archive is normaly downloaded to a deliveryfolder and unzipped there.

File Locations:

* a DownloadSource can Download from different Location (using Wget)
* the Jenkins Source is very useful when you want to transfer certain Build Artifacts from your Jenkins CI Server (see below for an example)

Folder Locations:

* Git
* SVN

Deployment Scripts Example
------------------------------

We recommend this structure:

 * deploy.php (your central deployment script, evaluating parameters and get things started)
 * EasyDeploy (EasyDeploy Git-Submodule)
 * EasyDeployWorkflows (EasyDeployWorkflows Git-Submodule)
 * Configuration (Workflow Configuration)

   * [Projectname]

     * [Instancename].php


The deploy.php triggers your deployment:
::
    <?php
    require_once dirname(__FILE__) . '/EasyDeployWorkflows/Classes/Autoloader.php';
    require_once dirname(__FILE__) . '/EasyDeploy/Classes/Utils.php';
    EasyDeploy_Utils::includeAll();
    $project = 'myprojectname';
    $environment = \EasyDeploy_Utils::getParameterOrUserSelectionInput('environment','Which environment do you want to install?',array('local','production'));

    try {
        $WebDeploymentWorkflow = $workflowFactory->createByConfigurationVariable($project,$environment,$releaseVersion, 'webWorkflowConfiguration');
        $WebDeploymentWorkflow->deploy();
    }
    catch (\EasyDeployWorkflows\Exception\HaltAndRollback $e) {
        exit(1);
    }

Lets say you have a environment "local" which is used to deploy the application on "localhost", then you would have a file "Configuration/myprojectname/local.php" with:
::
	<?php

	$source = new EasyDeployWorkflows\Source\File\JenkinsArtifactSource();
	$source
		->setJenkinsBaseUrl('your jenkins server')
		->setJobName('searchperience_experiencemanager_build')
		->setBuildNr('###version###')
		->setArtifactFileName('experiencemanager.tar.gz');

	$experiencemanagerConfiguration = new EasyDeployWorkflows\Workflows\Application\StandardApplicationConfiguration();
	$experiencemanagerConfiguration
			->addInstallServer('localhost')
			->setInstallationTargetFolder('###ENV:SPM_WEBROOT###')
			->setSource($source);

Workflow Configuration Example
------------------------------

Most of the configuration values support the following placeholders.

 * ###releaseversion### - will be replaces with the releaseversion of the workflow configuration
 * ###projectname### - will be replaces with the projectname of the workflow configuration
 * ###environment### or ###environmentname###- will be replaced with the environmentname of the instanceconfiguration
 * ###ENV:<value>### - will be replaced with the environment variable (Environmentvariable of the server that executes the deploy script!)

Also the RunCommand Task will export the Variables ENVIRONMENT,PROJECTNAME, RELEASEVERSION and RELEASEVERSION_ESCAPED - so they will be available in your command.

Sample deploy configuration (Configuration/projectname/aws.php)
::
    <?php
    $gitSource = new \EasyDeployWorkflows\Source\Folder\GitCloneSource();
    $gitSource->setRepository('ssh://git@yourgitrepository/mage/project.git')
    	->setTag('###releaseversion###');

    $magentoWorkflowConfiguration = new \EasyDeployWorkflows\Workflows\Application\MagentoApplicationConfiguration();
    $magentoWorkflowConfiguration
    	->addInstallServer('localhost')
    	->setReleaseBaseFolder($enviroment::getVariable('Magento_Webroot'))
    	->setSharedFolder('/var/www/qvc/shared')
    	->setSource($gitSource);

Logging:
-------------------------

There is a simple Logger singleton that is used to log to the screen and to a file.


The default file that is used for logging is "deploy-<releaseversion>-<date>.log".
The Logfiles are stored in the Instances LogFolder (defaults to the same folder like your deployment script) and can be set with:
::
   $instanceConfiguration->setDeployLogFolder('/var/log/');


You can also set a custom log file by:
::
   \EasyDeployWorkflows\Logger\Logger::getInstance()->setLogFile();


Workflow: ReleaseFolderApplicationWorkflow
----------------------------------
This is a typical best practice Workflow.
It deploys a common Application based on a available source and uses the Releasefolder Pattern:

<ReleaseBaseFolder>
   -  <ReleaseVersion1>
   -  <ReleaseVersion2>
   -  <ReleaseVersion3>
   -  current (Symlink to <ReleaseVersion2>)
   -  previous (Symlink to <ReleaseVersion1>)
   -  next (Symlink to <ReleaseVersion3> during deployment)

Your htdocs folder typically points to something like this:

- htdocs to <ReleaseBaseFolder>/current/Public
- htdocsNext to <ReleaseBaseFolder>/next/Public

It deploys the Application to multiple Servers and uses the following steps:
::
	$this->addTasksToDownloadFromSourceToReleaseFolder();
	$this->addUpdateNextSymlinkTask();
	$this->addPreSetupTasks();
	$this->addWriteVersionFileTask();
	$this->addSetupTasks();
	$this->addSymlinkSharedFoldersTasks();
	$this->addPostSetupTasks();
	$this->addSmokeTestTasks();
	$this->addSwitchTask();
	$this->addPostSwitchTasks();
	# Cleanup old releases
	$this->addCleanupTasks();

The workflow can be configured:
ReleaseFolderApplicationConfiguration:
+-------------------------+-----------------------------------------+
| Configuration           | Description                             |
+=========================+=========================================+
| setReleaseBaseFolder    | root of release folder (see above)      |
+-------------------------+-----------------------------------------+
| setSharedFolder         | Folder with shared ressources           |
+-------------------------+-----------------------------------------+
| addInstallServer        | The servers where the workflow should be executed        |
+-------------------------+-----------------------------------------+
| setSetupCommand         | Defaults to "rsync -az . ###targetfolder###"             |
|                         | use the common markers and the marker ###targetfolder### |
+-------------------------+-----------------------------------------+
| addPreSetupTask         | add as many Tasks like you want to be executed before Setup |
+-------------------------+-----------------------------------------+
| addPostSetupTask        | add as many Tasks like you want to be executed after Setup |
+-------------------------+-----------------------------------------+


Workflow: MagentoApplicationWorkflow
----------------------------------

Extends ReleaseFolderApplicationWorkflow and adds Magento Deployment Steps:

 * Expects:
  	* htdocs folder in the Code Source (with Magento Core Code)
  	* default Setup command is "./Setup/Setup.sh"
 * Symlinks media folder in shared ressources folder
 * As Smoke Test the script "php htdocs/shell/indexer.php status" is called

+-------------------------+-----------------------------------------+
| Configuration           | Description                             |
+=========================+=========================================+
| setReindexAllMode		  | set a reindex mode (triggerd before switch) Defaults to self::REINDEX_MODE_NONE       |
+-------------------------+-----------------------------------------+
| ....                    | see above (SimpleApplicationWorkflow )  |
+-------------------------+-----------------------------------------+


Workflow: InstallableApplicationWorkflow
----------------------------------
This is a simple Workflow that deploys a common Application based on a available archive.
It deploys the Application to multiple Servers and uses the following steps:

 1. Downloads the Artifact from the configured Source to all configured servers (to the delivery folder).
 2. Extract the Artifact on all configured servers (within the delivery folder)
 3. Install (or Setup): Runs a specified Setup command. (Per default it rsyncs the installation target folder)
 4. Cleanup the extracted Folder

The workflow can be configured:
Configuration:
+-------------------------+-----------------------------------------+
| Configuration           | Description                             |
+=========================+=========================================+
| setInstallTargetFolder  | used as input for the setupcommand      |
+-------------------------+-----------------------------------------+
| addInstallServer        | The servers where the workflow should be executed        |
+-------------------------+-----------------------------------------+
| setSetupCommand         | The Setup Script that is executed. The working Directory for the Execution is the Dowloaded Source. |
|                         | You can use the common markers and the marker ###targetfolder### |
|                         | Defaults to "rsync -az . ###targetfolder###"  |
+-------------------------+-----------------------------------------+
| addPreSetupTask         | add as many Tasks like you want to be executed before Setup |
+-------------------------+-----------------------------------------+
| addPostSetupTask        | add as many Tasks like you want to be executed after Setup |
+-------------------------+-----------------------------------------+

Workflow: SimpleApplicationWithNFSServerWorkflow
----------------------------------
Like SimpleApplicationWorkflow, but it expects, that there is a central NFS server that has the filesystem shared with potential frontend servers.
It deploys the Application to your infrastructure by doing the same step like using the ArchivedApplicationWorkflow only on the NFS server.
But followed by a Sync Script on all the configured Installservers (Frontendservers).

+-------------------------+-----------------------------------------+
| Configuration           | Description                             |
+=========================+=========================================+
| setNFSServer			  | set the name of nfs server (only on the nfs server the application is downloaded and installed)             |
+-------------------------+-----------------------------------------+
| setSyncFromNFSScript    | this script is executed on all given install servers      |
+-------------------------+-----------------------------------------+
| ....                    | see above (SimpleApplicationWorkflow )  |
+-------------------------+-----------------------------------------+

Try Run
--------------------------

Most of the tasks are not executed if you set the global tryRun flag:
::
    $GLOBALS['tryRun'] = true


Tipps: Configuring your Application
--------------------------
Each application should have a way to configure itself to the environment.
For example the domainname and all data to access dependencies and resources (database, cache backends, other servers etc).
This is best done by the application itself, therefore the Workflows above call a configured script. For example
::
	configure.php --environment=<passedenvironmentname>

Best practice here, is to read everything from the systems environment variables.
And it should be part of the provisioning script to set the correct Environment variables.

Export a Shell variable. This makes the variable available for subprocesses:
::
  export APPLICATION_DB_NAME="magento"

Read it in php
::
  getenv('APPLICATION_DB_NAME')

Read it in shell
::
	#!/bin/bash
    echo ${APPLICATION_DB_NAME};

You should also check for https://github.com/AOEmedia/EnvSettingsTool, you may want to include this in your application and use it for configuration.




.. |buildStatusIcon| image:: https://travis-ci.org/AOEmedia/EasyDeployWorkflows.png?branch=refactorworkflows
:alt: Build Status
   :target: http://travis-ci.org/AOEmedia/EasyDeployWorkflows