<?php
/**
 * Binary for deploying a searchperience-backend instance
 * On devbox:
 *  deploy.php --version=12 --environment=local
 */
require_once dirname(__FILE__) . '/vendor/autoload.php';

$project = 'axalta';

$environment = \EasyDeploy_Utils::getParameterOrUserSelectionInput('environment','Which environment do you want to install?',array('devbox'));
$releaseVersion = \EasyDeploy_Utils::getParameterOrInput('version','Which version?');

$workflowFactory = new EasyDeployWorkflows\Workflows\WorkflowFactory();

$MagentoWorkflowRequest = new EasyDeployWorkflows\Workflows\WorkflowRequest();
$MagentoWorkflowRequest->setProjectName($project);
$MagentoWorkflowRequest->setEnvironmentName($environment);
$MagentoWorkflowRequest->setReleaseVersion($releaseVersion);
$MagentoWorkflowRequest->setConfigurationKey('local');
$MagentoWorkflowRequest->setWorkFlowConfigurationVariableName('magentoConfiguration');


try {
    $deploymentWorkflow = $workflowFactory->createByRequest($MagentoWorkflowRequest);
    $deploymentWorkflow->deploy();
}
catch (\EasyDeployWorkflows\Exception\HaltAndRollback $e) {
    exit(1);
}