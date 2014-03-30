<?php
namespace Bun\Logger\Tool;

use Bun\Logger\LoggerInterface;
use Bun\Tool\ConsoleResponse;
use Bun\Tool\Controller\ToolController;

/**
 * Class LoggerController
 *
 * @package Bun\Logger\Tool
 */
class LoggerController extends ToolController
{
    /**
     * Little help
     * @return ConsoleResponse
     */
    protected function indexAction()
    {
        self::out('Logger config levels:', "\n", 'light_green');
        $all = 'debug | info | warning | error';
        $info = 'info | warning | error';
        $warning = 'warning | error';
        $error = 'error';
        $levels = array(
            $all     =>
                LoggerInterface::LOG_LEVEL_DEBUG | LoggerInterface::LOG_LEVEL_INFO |
                LoggerInterface::LOG_LEVEL_WARNING | LoggerInterface::LOG_LEVEL_ERROR,
            $info    =>
                LoggerInterface::LOG_LEVEL_INFO |
                LoggerInterface::LOG_LEVEL_WARNING | LoggerInterface::LOG_LEVEL_ERROR,
            $warning => LoggerInterface::LOG_LEVEL_WARNING | LoggerInterface::LOG_LEVEL_ERROR,
            $error   => LoggerInterface::LOG_LEVEL_ERROR,
        );

        self::out('');
        self::out('- Log all: ' . $levels[$all] . ' = ' . $all, "\n", 'green');
        self::out('- Log info: '. $levels[$info] . ' = '. $info, "\n", 'cyan');
        self::out('- Log warning: '. $levels[$warning] . ' = '. $warning, "\n", 'yellow');
        self::out('- Log error: '. $levels[$error] . ' = '. $error, "\n", 'light_red');

        return new ConsoleResponse('');
    }

    protected function helpAction()
    {
        return $this->indexAction();
    }
}