# Deployer Docker recipe

Add functionality to run deployment tasks in Docker containers. This package also contains some tasks specific for 
deployment of Symfony application. 


## Installation

`composer require accurateweb/deployer-docker-recipe@dev-master`

## Configuration



## Usage
 
While deploying a dockerized application you might need to perform some tasks inside the corresponding Docker containers
instead of a deployment host machine. 

You will need a separate container with php and composer installed in order to run php based commands. This is inspired 
by the Laradock workspace concept

Your deployment scenario might look like this:

- Create a release directory as usual
- Update code via git or rsync
- Build a workspace image with php and composer installed or pull it from docker container registry   
- Instead of running php-specific deployment tasks on host machine, run them in the workspace container.  

For this purpose you can call either dockerComposeRun or dockerComposeExec function which in
turn will call a Deployer run function inside a Docker container using docker run or docker exec correspondingly.   

### Example deploy task

~~~php
namespace Deployer;

require 'recipe/docker-symfony.php';

// ...

task('deploy', [
  'deploy:info',
  'deploy:prepare',
  'deploy:lock',
  'deploy:release',
  'deploy:update_code',
  'deploy:clear_paths',
  'deploy:create_cache_dir',
  'deploy:shared',
  'deploy:assets',
  'deploy:docker:prepare',
  'deploy:docker:vendors',
  'deploy:docker:assets:install',
  'deploy:docker:cache:clear',
  'deploy:docker:cache:warmup',
  'deploy:writable',
  'deploy:docker:database:migrate',
  'deploy:docker:start_services',
  'deploy:symlink',
  'deploy:unlock',
  'cleanup',
])->desc('Deploy your project');
~~~

