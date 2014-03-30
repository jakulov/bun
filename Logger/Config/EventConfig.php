<?php
namespace Bun\Logger\Config;

use Bun\Core\Config\AbstractConfig;

class EventConfig extends AbstractConfig
{
    protected $name = 'event';

    protected $config = array(
        'bun.core.request' => array(
            '@bun.logger.event_listener.request' => 'onRequest',
        ),
        'bun.core.after_response' => array(
            '@bun.logger.event_listener.response' => 'onAfterResponse'
        ),
        'bun.core.after_shutdown' => array(
            '@bun.logger.event_listener.shutdown' => 'onAfterShutdown',
        ),
    );
}