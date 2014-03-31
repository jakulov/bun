<?php
namespace Bun\Session\Config;

use Bun\Core\Config\AbstractConfig;

/**
 * Class EventConfig
 *
 * @package Bun\Session\Config
 */
class EventConfig extends AbstractConfig
{
    protected $name = 'event';

    protected $config = array(
        'bun.core.before_response' => array(
            '@bun.session.event_listener.response' => 'onBeforeResponse'
        ),
    );
}