<?php
namespace Bun\PDO\Config;

use Bun\Core\Config\AbstractConfig;

/**
 * Class EventConfig
 *
 * @package Bun\PDO\Config
 */
class EventConfig extends AbstractConfig
{
    protected $name = 'event';

    protected $config = array(
        'bun.pdo.query' => array(
            '@bun.pdo.event_listener.query' => 'onQuery'
        )
    );
}