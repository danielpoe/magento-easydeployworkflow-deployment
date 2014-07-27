<?php

/**
 * Specify the Source of the artifact that should be installed.
 * Check https://github.com/AOEpeople/EasyDeployWorkflows/tree/refactorworkflows for a list of available sources.
 * (The source should take the "releaseversion" into account - to point to the correct version of course)
 */
$source = new \EasyDeployWorkflows\Source\File\JenkinsArtifactSource();
$source
    ->setJenkinsBaseUrl('http://development-axalta.aoe-works.de:8080')
    ->setJobName('axalta-shop_build')
    ->setBuildNr('###releaseversion###')
    ->setArtifactFileName('artifacts/axalta-shop.tar.gz')
    ->setUser('deployment')
    ->setPassword('start')
	->setFolderNameInArchive('');

$magentoConfiguration = new EasyDeployWorkflows\Workflows\Application\MagentoApplicationConfiguration();
$magentoConfiguration
        ->addInstallServer('localhost')
        ->setReleaseBaseFolder('###ENV:RELEASEBASEFOLDER###')
		->setSource($source)
		->setDeliveryFolder('###ENV:DELIVERYFOLDER###')
		->setReindexAllMode(EasyDeployWorkflows\Workflows\Application\MagentoApplicationConfiguration::REINDEX_MODE_BACKGROUND)
		->setSetupCommand('./install.sh');



//$experiencemanagerConfiguration->addPostSetupTask();
