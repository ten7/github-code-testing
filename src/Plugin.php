<?php

namespace TEN7\CodeTests;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    protected Composer $composer;
    protected IOInterface $io;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io): void {}

    public function uninstall(Composer $composer, IOInterface $io): void {}

    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'scaffold',
            ScriptEvents::POST_UPDATE_CMD  => 'scaffold',
        ];
    }

    public function scaffold(Event $event): void
    {
        $extra  = $this->composer->getPackage()->getExtra();
        $config = $extra['code_testing']['github'] ?? [];

        if (empty($config)) {
            $this->io->write('<info>ten7/code-testing: no code_testing config found, skipping scaffold.</info>');
            return;
        }

        $scaffoldDir = dirname(__DIR__) . '/scaffold/.github';
        $projectDir  = dirname($this->composer->getConfig()->get('vendor-dir'));
        $targetDir   = $projectDir . '/.github';

        // Always copy all actions — they are lightweight and only invoked when referenced.
        $this->copyDirectory($scaffoldDir . '/actions', $targetDir . '/actions');

        // Copy only the workflows declared in extra.code_testing.github.
        // Each context key becomes a filename prefix:
        //   "drainpipe": ["PlaywrightTests"] → drainpipePlaywrightTests.yml
        //   "deployment": ["PlaywrightTests"] → deploymentPlaywrightTests.yml
        //   "pantheon":   ["ReviewApps"]      → pantheonReviewApps.yml
        foreach ($config as $context => $items) {
            foreach ($items as $item) {
                $filename = $context . $item . '.yml';
                $source   = $scaffoldDir . '/workflows/' . $filename;
                $target   = $targetDir . '/workflows/' . $filename;

                if (!file_exists($source)) {
                    $this->io->writeError("<warning>ten7/code-testing: scaffold file not found: $filename</warning>");
                    continue;
                }

                if (!is_dir(dirname($target))) {
                    mkdir(dirname($target), 0755, true);
                }

                copy($source, $target);
                $this->io->write("<info>ten7/code-testing: scaffolded $filename</info>");
            }
        }
    }

    private function copyDirectory(string $source, string $target): void
    {
        if (!is_dir($source)) {
            return;
        }

        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $targetPath = $target . '/' . $iterator->getSubPathname();
            if ($item->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                copy($item->getPathname(), $targetPath);
                $this->io->write('<info>ten7/code-testing: scaffolded action ' . $iterator->getSubPathname() . '</info>');
            }
        }
    }
}
