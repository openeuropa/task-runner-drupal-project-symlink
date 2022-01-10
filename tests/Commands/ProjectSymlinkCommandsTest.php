<?php

declare(strict_types=1);

namespace OpenEuropa\DrupalProjectSymlink\Tests\Commands;

use Composer\Autoload\ClassLoader;
use OpenEuropa\TaskRunner\Services\Composer;
use OpenEuropa\TaskRunner\TaskRunner;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use PHPUnit\Framework\TestCase;

/**
 * Test project symlink command.
 */
class ProjectSymlinkCommandsTest extends TestCase
{

    /**
     * Test project symlink command.
     *
     * @param array $configuration
     *   Task runner configuration, it will be dumped in a runner.yml file.
     * @param array $project
     *   Array describing the test project, as in: its name, type and content.
     * @param array $expectedSymlinks
     *   List of expected symlinks, as an hash of "source: destination".
     * @param array $expectedMissingSymlinks
     *   List of symlinks that shouldn't be created.
     *
     * @dataProvider dataProvider
     */
    public function testProjectSymlinkCommands(array $configuration, array $project, array $expectedSymlinks, array $expectedMissingSymlinks)
    {
        // Create test project content.
        $fs = new Filesystem();
        $fs->remove($this->getSandboxRoot());
        foreach ($project['content']['directories'] as $name) {
            $fs->mkdir($this->getSandboxRoot().'/'.$name);
        }
        foreach ($project['content']['files'] as $name) {
            $fs->touch($this->getSandboxRoot().'/'.$name);
        }

        // Create test task runner configuration file.
        $configFile = $this->getSandboxFilepath('runner.yml');
        file_put_contents($configFile, Yaml::dump($configuration));

        $input = new StringInput("drupal:symlink-project --working-dir=".$this->getSandboxRoot());
        $output = new BufferedOutput();
        $runner = new TaskRunner($input, $output, $this->getClassLoader());

        // Mock composer service by settings test project name and type.
        $mock = $this->createMock(Composer::class);
        $mock->method('getProject')->willReturn($project['name']);
        $mock->method('getType')->willReturn($project['type']);
        $runner->getContainer()->extend('task_runner.composer')->setConcrete($mock);

        // Run command.
        $runner->run();

        // Assert that links are created correctly.
        foreach ($expectedSymlinks as $link => $target) {
            $filepath = $this->getSandboxFilepath($link);
            $this->assertFileExists($filepath);
            $this->assertEquals($target, readlink($filepath));
        }

        // Assert that links that are not supposed to be create, are not.
        foreach ($expectedMissingSymlinks as $link) {
            $this->assertFileDoesNotExist($this->getSandboxFilepath($link));
        }
    }

    /**
     * Test data provider.
     *
     * @return array
     */
    public function dataProvider(): array
    {
        return Yaml::parse(file_get_contents(__DIR__.'/../fixtures/symlink-project.yml'));
    }

    /**
     * Get local test sandbox directory.
     *
     * @return string
     */
    protected function getSandboxRoot(): string
    {
        return __DIR__."/../sandbox";
    }

    /**
     * Get full path of a file in the sandbox directory.
     *
     * @param string $name
     *   File name, relative to the sandbox directory.
     *
     * @return string
     */
    protected function getSandboxFilepath($name): string
    {
        return $this->getSandboxRoot().'/'.$name;
    }

    /**
     * Get project class loader.
     *
     * @return \Composer\Autoload\ClassLoader
     */
    protected function getClassLoader(): ClassLoader
    {
        return require __DIR__.'/../../vendor/autoload.php';
    }
}
