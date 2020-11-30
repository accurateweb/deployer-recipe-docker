<?php

namespace Accurateweb\Deployer;

use function Deployer\desc;
use function Deployer\get;
use function Deployer\task;

require 'docker.php';

/**
 * Install assets from public dir of bundles
 */
task('deploy:docker:assets:install', function () {
  dockerExec(get('docker_compose_php_service'), '{{bin/php}} {{bin/console}} assets:install {{console_options}} {{docker_deploy_path}}/web', [
    'user' => 'laradock'
  ]);
})->desc('Install bundle assets');


/**
 * Dump all assets to the filesystem
 */
task('deploy:docker:assetic:dump', function () {
  if (get('dump_assets')) {
    dockerExec(get('docker_compose_php_service'), '{{bin/php}} {{bin/console}} assetic:dump {{console_options}}', [
      'user' => 'laradock'
    ]);
  }
})->desc('Dump assets');

/**
 * Clear Cache
 */
task('deploy:docker:cache:clear', function () {
  dockerExec(get('docker_compose_php_service'), '{{bin/php}} {{bin/console}} cache:clear {{console_options}} --no-warmup', [
    'user' => 'laradock'
  ]);
})->desc('Clear cache');

/**
 * Warm up cache
 */
task('deploy:docker:cache:warmup', function () {
  dockerExec(get('docker_compose_php_service'), '{{bin/php}} {{bin/console}} cache:warmup {{console_options}}', [
    'user' => 'laradock'
  ]);
})->desc('Warm up cache');


/**
 * Migrate database
 */
task('deploy:docker:database:migrate', function () {
  $options = '{{console_options}} --allow-no-migration';
  if (get('migrations_config') !== '') {
    $options = sprintf('%s --configuration={{release_path}}/{{migrations_config}}', $options);
  }

  dockerExec(get('docker_compose_php_service'), sprintf('{{bin/php}} {{bin/console}} doctrine:migrations:migrate %s', $options));
})->desc('Migrate database');

desc('Installing vendors');
task('deploy:docker:vendors', function () {
  dockerExec(get('docker_compose_php_service'), '{{bin/composer}} {{composer_options}}', [
    'user' => 'laradock'
  ]);
});
