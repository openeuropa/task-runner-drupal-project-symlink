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

You can also prevent files and/or directories from being symlinked by using the following configuration
in your `runner.yml` file, as showed in the example below:

    drupal_project_symlink:
      ignore:
        - directory-to-ignore
        - file-to-ignore.txt


## Step debugging

To enable step debugging from the command line, pass the `XDEBUG_SESSION` environment variable with any value to
the Docker container:

```bash
docker-compose exec -e XDEBUG_SESSION=1 web <your command>
```

Please note that, starting from XDebug 3, a connection error message will be outputted in the console if the variable is
set but your client is not listening for debugging connections. The error message will cause false negatives for PHPUnit
tests.

To initiate step debugging from the browser, set the correct cookie using a browser extension or a bookmarklet
like the ones generated at https://www.jetbrains.com/phpstorm/marklets/.
