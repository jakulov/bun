<?php
namespace Bun\PDO\Config\Dev;

use Bun\Core\Config\AbstractConfig;
use Bun\Core\Config\EnvConfigInterface;

/**
 * Class LoggerConfig
 *
 * @package Bun\PDO\Config\Dev
 */
class LoggerConfig extends AbstractConfig implements EnvConfigInterface
{
    protected $name = 'logger';

    protected $config = array(
        'loggers' => array(
            'pdo_query' => array(
                'level' => 30 // all
            )
        )
    );
}