<?php
namespace Bun\Core\Config;

/**
 * Class EventConfig
 *
 * @package Bun\Core\Config
 */
class EventConfig extends AbstractConfig
{
    protected $name = 'event';

    protected $config = array(
        'bun.core.request'        => array(
            '@bun.logger.event_listener.request' => 'onRequest',
        ),
        'bun.core.after_shutdown' => array(
            '@bun.logger.event_listener.shutdown' => 'onAfterShutdown',
        ),
    );
}