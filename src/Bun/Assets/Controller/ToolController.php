<?php
namespace Bun\Assets\Controller;

use Bun\Core\ApplicationInterface;
use Bun\Tool\ConsoleResponse;

/**
 * Class ToolController
 *
 * @package Bun\Assets\Controller
 */
class ToolController extends \Bun\Tool\Controller\ToolController
{
    /**
     * @return ConsoleResponse
     */
    protected function indexAction()
    {
        return $this->helpAction();
    }

    /**
     * @return ConsoleResponse
     */
    protected function installAction()
    {
        self::out('');
        self::out('Installing assets...', "\n", 'cyan');

        if(ENV !== ApplicationInterface::APPLICATION_ENV_DEV) {
            $installed = $this->getAssetManager()->install();

            foreach ($installed['bun'] as $module => $assets) {
                self::out('Bun/' . $module . ' installed ' . $assets . ' assets', "\n", ($assets > 0) ? 'green' : 'light_gray');
            }
            foreach ($installed['app'] as $module => $assets) {
                self::out($module . ' installed: ' . $assets . ' assets', "\n", ($assets > 0) ? 'light_green' : 'light_gray');
            }
            foreach ($installed['config'] as $module => $assets) {
                self::out($module . ' config installed: ' . $assets . ' assets', "\n", ($assets > 0) ? 'light_green' : 'light_gray');
            }

            self::out("\n" . 'Installing assets done', "\n");
        }
        else {
            self::out("\n" . 'Skip installing assets in DEV environment', "\n", 'yellow');
        }

        return new ConsoleResponse();
    }

    /**
     * @return ConsoleResponse
     */
    protected function clearAction()
    {
        self::out('');
        if (!$this->hasConsoleArgument('force')) {
            if (!self::promt('You sure you want to clear installed assets: [Y/N]')) {
                return new ConsoleResponse('Task cancelled', ConsoleResponse::RESPONSE_CANCELED);
            }
        }
        self::out('Clearing assets...', "\n", 'cyan');

        $this->getAssetManager()->clear();

        self::out("\n" . 'Clearing assets done', "\n");

        return new ConsoleResponse();
    }

    /**
     * @return ConsoleResponse
     */
    protected function helpAction()
    {
        self::out('');
        self::out('Welcome to Bun asset manager tool', "\n", 'light_green');
        $commands = array(
            'bun.assets:install' => 'Refreshing installation of all system assets',
            'bun.assets:clear'   => 'Clearing installation of all system assets',
        );
        $this->outCommandsHelp($commands);

        return new ConsoleResponse();
    }
}