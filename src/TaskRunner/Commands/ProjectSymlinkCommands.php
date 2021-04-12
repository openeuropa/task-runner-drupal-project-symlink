<?php

declare(strict_types=1);

namespace OpenEuropa\DrupalProjectSymlink\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use OpenEuropa\TaskRunner\Contract\ComposerAwareInterface;
use OpenEuropa\TaskRunner\Traits\ComposerAwareTrait;
use Robo\Collection\CollectionBuilder;

/**
 * Project symlink commands.
 */
class ProjectSymlinkCommands extends AbstractCommands implements ComposerAwareInterface
{
    use ComposerAwareTrait;

    /**
     * Symlink current project within a target site build.
     *
     * @return \Robo\Collection\CollectionBuilder
     *
     * @command drupal:symlink-project
     */
    public function symlink(): CollectionBuilder
    {
        $workingDir = $this->getConfig()->get('runner.working_dir');
        // Get linkable files and exit, if none available.
        $linkableFiles = $this->getLinkableFiles($workingDir);
        if (empty($linkableFiles)) {
            return $this->collectionBuilder();
        }

        $drupalRoot = $this->getConfig()->get('drupal.root');
        $projectType = $this->composer->getType();
        $projectTypeDirectory = $this->getProjectTypeDirectory($projectType);
        $projectDirectory = $drupalRoot.DIRECTORY_SEPARATOR.$projectTypeDirectory.DIRECTORY_SEPARATOR.$this->composer->getProject();

        // Recreate target project directory.
        $tasks = [
            $this->taskFilesystemStack()->remove([$projectDirectory]),
            $this->taskFilesystemStack()->mkdir($projectDirectory),
        ];

        // When linking project files, step back from the current target as many
        // times as the project directory's depth.
        $steps = preg_replace('/\w+/', '..', $projectDirectory);
        foreach ($this->getLinkableFiles($workingDir) as $file) {
            // Link source with target.
            $tasks[] = $this->taskFilesystemStack()->symlink($steps.DIRECTORY_SEPARATOR.$file, $projectDirectory.DIRECTORY_SEPARATOR.$file);
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Return current project type base directory.
     *
     * @param string $projectType
     *   Drupal project type, either 'drupal-module', 'drupal-theme' or 'drupal-profile'.
     * @return string
     *   Base directory name, either "modules", "themes" or "profiles".
     */
    protected function getProjectTypeDirectory(string $projectType): string
    {
        $mapping = [
            'drupal-module' => 'modules',
            'drupal-theme' => 'themes',
            'drupal-profile' => 'profiles',
        ];
        if (!array_key_exists($projectType, $mapping)) {
            throw new \RuntimeException("Missing or not supported Drupal project type in composer.json. Supported types: 'drupal-module', 'drupal-theme' and 'drupal-profile'.");
        }

        return $mapping[$projectType];
    }

    /**
     * Get linkable files from a given directory.
     *
     * @param string $directory
     *   Directory to be scanned for linkable files.
     *
     * @return array
     *   List of linkable files.
     */
    protected function getLinkableFiles(string $directory): array
    {
        $ignore = [
            '.',
            '..',
            '.git',
            'vendor',
            $this->getConfig()->get('drupal.root'),
        ];
        $list = scandir($directory);

        return array_diff($list, $ignore);
    }
}
