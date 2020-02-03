<?php
namespace Deployer;

require 'recipe/common.php';

inventory('hosts.yml');

// Project name
//set('application', 'my_project');

// Project repository
set('repository', 'https://github.com/fiskhandlarn/dontvis.it.git');

// [Optional] Allocate tty for git clone. Default value is false.
//set('git_tty', true);

set('keep_releases', 3);

// Shared files/dirs between deploys
//set('shared_files', []);
//set('shared_dirs', []);

// Writable dirs by web server
//set('writable_dirs', []);

// Hosts

host('qa')
    ->set('branch', 'qa');

host('prod')
    ->set('branch', 'master');

// Tasks

desc('Deploy your project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    //'deploy:vendors',
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);

task('git:save_info', function () {
    run('cd {{release_path}} && git log -1 -b {{branch}} > {{release_path}}/.gitlog');
});

task('copy:env', function () {
    run('cp {{deploy_path}}/{{target}}.env {{release_path}}/.env');
});

task('build:composer', function () {
    // phpfpm locks the autoloader file, let's not do this:
    // if (has('previous_release')) {
    //     run('if [ -d {{previous_release}}/vendor ]; then cp -R {{previous_release}}/vendor {{release_path}}/vendor; fi');
    // }

    run('cd {{release_path}} && composer install');
});

task('build:npm', function () {
    if (has('previous_release')) {
        run('if [ -d {{previous_release}}/node_modules ]; then cp -R {{previous_release}}/node_modules {{release_path}}/node_modules; fi');
    }

    run('cd {{release_path}} && npm install && npm run prod', [
        'timeout' => 1800,
    ]);
});

task('link:storage', function () {
    run('mkdir -p {{deploy_path}}/shared/storage');
    run('chmod o+w {{deploy_path}}/shared/storage');
    run('ln -s {{deploy_path}}/shared/storage {{release_path}}/storage');
});

task('restart:phpfpm', function () {
    run('sudo /usr/sbin/service php7.2-fpm restart');
});

after('deploy:update_code', 'git:save_info');
after('deploy:update_code', 'copy:env');
after('deploy:update_code', 'build:composer');
after('deploy:update_code', 'build:npm');
after('deploy:update_code', 'link:storage');

after('success', 'restart:phpfpm');

// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
