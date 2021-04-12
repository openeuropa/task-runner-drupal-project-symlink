# OpenEuropa Task Runner: Drupal project symlink

The Drupal project symlink [Task Runner](https://github.com/openeuropa/task-runner) command aims at simplifying Drupal
development, when using a Composer-based workflow.

Assuming that, as result of running `composer install`, a Drupal site is build in the current Drupal project directory,
this command will symlink project files within that Drupal site, by respecting Drupal coding conventions. It will also
avoid to symlink directories like the current Drupal site root and the `vendor` directory, so to avoid a code recursion.

## Installation

Require the command as a dev dependency:

    composer require --dev openeuropa/task-runner-drupal-project-symlink

Make sure the command runs after `composer install`:

    "scripts": {
        "post-install-cmd": "./vendor/bin/run drupal:symlink-project",
    },

## Usage

Make sure to always run the following command, after adding/removing files or directories in the project root:

    ./vendor/bin/run drupal:symlink-project
