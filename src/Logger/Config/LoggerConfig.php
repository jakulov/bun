<?php
namespace Bun\Logger\Config;

use Bun\Core\Config\AbstractConfig;
use Bun\Logger\LoggerInterface;

/**
 * Class LoggerConfig
 *
 * @package Bun\Logger
 */
class LoggerConfig extends AbstractConfig
{
    protected $name = 'logger';

    protected $config = array(
        'dir' => 'log',
        // LoggerInterface::LOG_LEVEL_INFO | LoggerInterface::LOG_LEVEL_WARNING | LoggerInterface::LOG_LEVEL_ERROR
        'level' => 28,
        'buffer' => true,
        'prefix' => 'bun',
        'max_size' => 10000000, // Kb
        'outdated' => 29376000,
        'loggers' => array(
            'runtime' => array(
                'level' => LoggerInterface::LOG_LEVEL_INFO
            )
        )
    );
}