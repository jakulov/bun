<?php
namespace Bun\Core\Controller;

use Bun\Core\Config\ApplicationConfig;
use Bun\Tool\ConsoleResponse;
use Bun\Tool\Controller\ToolController;
use Bun\Core\Storage\FileStorage;

/**
 * Class CacheController
 *
 * @package Bun\Core\Controller
 */
class CacheController extends ToolController
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
    protected function clearAction()
    {
        $configResponse = $this->clearConfigAction();
        $storageResponse = $this->clearStorageAction();
        $resultCode =
            (
                $configResponse->getResultCode() === ConsoleResponse::RESPONSE_FAIL ||
                $storageResponse->getResultCode() === ConsoleResponse::RESPONSE_FAIL
            ) ?
                ConsoleResponse::RESPONSE_FAIL :
                ConsoleResponse::RESPONSE_OK;

        return new ConsoleResponse('', $resultCode);
    }

    /**
     * @return ConsoleResponse
     */
    protected function clearConfigAction()
    {
        $appName = $this->application->getApplicationName();
        $configCacheFile = VAR_DIR . DIRECTORY_SEPARATOR . ApplicationConfig::CONFIG_CACHE_DIR .
            DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR . 'config.cache';

        self::out('');

        if (file_exists($configCacheFile)) {
            self::out('Removing config cache file: ' . $configCacheFile, "\n", 'light_green');
            $removed = unlink($configCacheFile);
            if (!$removed) {
                self::out('Unable to unlink file, check permissions', "\n", 'red');

                return new ConsoleResponse('', ConsoleResponse::RESPONSE_FAIL);
            }
        }
        else {
            self::out('Cache file not found: ' . $configCacheFile, "\n", 'yellow');
        }

        return new ConsoleResponse('', ConsoleResponse::RESPONSE_OK);
    }

    /**
     * @return ConsoleResponse
     */
    protected function clearStorageAction()
    {
        /** @var FileStorage $fileStorage */
        $fileStorage = $this->getContainer()->get('bun.core.file_storage');

        $cleared = $fileStorage->clearStorageCache();
        self::out('');
        if ($cleared) {
            self::out('FileStorage cache cleared', "\n", 'light_green');

            return new ConsoleResponse();
        }

        return new ConsoleResponse('Failed to clear FileStorage cache', ConsoleResponse::RESPONSE_FAIL);
    }

    /**
     * @return ConsoleResponse
     */
    protected function helpAction()
    {
        self::out('');
        self::out('Welcome to core cache controller', "\n", 'light_green');

        $commands = array(
            'bun.core.cache:clear'        => 'Clears both config cache and FileStorage query cache',
            'bun.core.cache:clearConfig'  => 'Clears config cache',
            'bun.core.cache:clearStorage' => 'Clears FileStorage query cache',
        );

        self::outCommandsHelp($commands);

        return new ConsoleResponse('');
    }
}