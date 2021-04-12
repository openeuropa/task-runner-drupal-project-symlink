<?php

declare(strict_types=1);

namespace OpenEuropa\DrupalProjectSymlink\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use OpenEuropa\TaskRunner\Contract\ComposerAwareInterface;
use OpenEuropa\TaskRunner\Traits\ComposerAwareTrait;

/**
 * Project symlink commands.
 */
class ProjectSymlinkCommands extends AbstractCommands implements ComposerAwareInterface
{
    use ComposerAwareTrait;

    /**
     * Symlink current project within a target site build.
     *
     * @command drupal:symlink-project
     */
    public function symlink()
    {
        $drupalRoot = $this->getConfig()->get("drupal.root");
        $projectDirectory = $drupalRoot.DIRECTORY_SEPARATOR.$this->getProjectTypeDirectory().DIRECTORY_SEPARATOR.$this->composer->getProject();

        $tasks = [
            // Make sure we do not have a release directory yet.
            $this->taskFilesystemStack()->remove([$projectDirectory]),
        ];

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Return current project type base directory.
     *
     * @return string
     *   Base directory name, either "modules", "themes" or "profiles".
     */
    protected function getProjectTypeDirectory()
    {
        $mapping = [
            'drupal-module' => 'modules',
            'drupal-theme' => 'themes',
            'drupal-profile' => 'profile',
        ];
        $projectType = $this->composer->getType();
        if (!array_key_exists($projectType, $mapping)) {
            throw new \RuntimeException("Missing or not supported Drupal project type in composer.json. Supported types: 'drupal-module', 'drupal-theme' and 'drupal-profile'.");
        }

        return $mapping[$projectType];
    }
}
