<?php
namespace Bun\Logger\Config\Dev;

use Bun\Core\Config\AbstractConfig;
use Bun\Core\Config\EnvConfigInterface;
use Bun\Logger\LoggerInterface;

/**
 * Class LoggerConfig
 *
 * @package Bun\Logger
 */
class LoggerConfig extends AbstractConfig implements EnvConfigInterface
{
    protected $name = 'logger';

    protected $config = array(
        'loggers' => array(
            'runtime' => array(
                'level' => LoggerInterface::LOG_LEVEL_DEBUG
            )
        )
    );
}