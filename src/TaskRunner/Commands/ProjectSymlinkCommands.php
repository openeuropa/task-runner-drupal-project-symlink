<?php

declare(strict_types=1);

namespace OpenEuropa\DrupalProjectSymlink\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use OpenEuropa\TaskRunner\Contract\ComposerAwareInterface;
use OpenEuropa\TaskRunner\Traits\ComposerAwareTrait;
use Symfony\Component\Console\Input\InputOption;

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
    }
}
