<?php

namespace Accurateweb\Deployer;

use function Deployer\get;
use function Deployer\set;
use function Deployer\run;
use function Deployer\parse;
use function Deployer\Support\array_to_string;

/*
 * Specify a directory containing docker-compose.yml file(s)
 */
set('docker_compose_dir', function(){
  return get('release_path');
});

set('bin/docker', 'docker');
set('bin/docker-compose', 'docker-compose');

set('docker_compose_php_service', 'php');

/**
 * Return Docker container id by service name
 *
 * @param $service
 * @return string
 */
function dockerComposeGetContainerId($service)
{
  return run('cd {{docker_compose_dir}} && {{bin/docker-compose}} ps -q ' . $service);
}

/**
 * Return true if a given service is running in Docker, otherwise false
 *
 * @param $service
 * @return bool
 */
function dockerServiceExists($service)
{
  return !!dockerComposeGetContainerId($service);
}

/**
 * Remove container from Docker by service name
 *
 * @param $service
 * @return string
 */
function dockerRemoveService($service)
{
  $containerId = dockerComposeGetContainerId($service);

  if (!$containerId)
  {
    throw new \InvalidArgumentException(`Container ${service} not found.`);
  }

  return run('{{bin/docker}} rm -f ' . $containerId);
}

/**
 * Prepare a command to be executed in Docker container
 *
 * @param $command
 * @param array $options
 * @return string
 */
function dockerPrepareCommand($command, $options = [])
{
  $command = parse($command);
  $workingPath = get('working_path', '');

  if (!empty($workingPath))
  {
    $command = "cd $workingPath && ($command)";
  }

  $env = get('env', []) + ($options['env'] ?? []);
  if (!empty($env))
  {
    $env = array_to_string($env);
    $command = "export $env; $command";
  }

  return $command;
}

/**
 * Run a command using service, specified in docker-compose.yml file using docker exec
 *
 * @param $service
 * @param $command
 * @param array $options
 */
function dockerExec($service, $command, $options = [])
{
  $containerId = dockerComposeGetContainerId($service);

  if (!$containerId)
  {
    throw new \InvalidArgumentException(`Container ${service} not found.`);
  }

  $command = dockerPrepareCommand($command, $options);

  $opts = [];
  if (isset($options['user']))
  {
    $opts[] = '-u ' . $options['user'];
  }

  $command = sprintf('{{bin/docker}} exec %s %s sh -c "%s"', implode(' ', $opts), $service, $command);

  return run($command);
}


/**
 * Run a command using service, specified in docker-compose.yml file using docker run
 *
 * @param $service
 * @param $command
 * @param array $options
 */
function dockerRun($service, $command, $options = [])
{
  $containerId = dockerComposeGetContainerId($service);

  if (!$containerId)
  {
    throw new \InvalidArgumentException(`Container ${service} not found.`);
  }

  $command = dockerPrepareCommand($command, $options);

  $opts = [];
  if (isset($options['user']))
  {
    $opts[] = '-u ' . $options['user'];
  }

  $command = sprintf('{{bin/docker}} run %s %s sh -c "%s"', implode(' ', $opts), $service, $command);

  return run($command);
}
