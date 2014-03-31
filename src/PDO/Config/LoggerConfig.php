<?php
namespace Bun\PDO\Config;

use Bun\Core\Config\AbstractConfig;

/**
 * Class LoggerConfig
 *
 * @package Bun\PDO\Config
 */
class LoggerConfig extends AbstractConfig
{
    protected $name = 'logger';

    protected $config = array(
        'loggers' => array(
            'pdo_query' => array(
                'level' => 24,
            )
        )
    );
}