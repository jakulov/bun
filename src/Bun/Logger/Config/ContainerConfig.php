<?php
namespace Bun\Logger\Config;

use Bun\Core\Config\AbstractConfig;

/**
 * Class ContainerConfig
 *
 * @package Bun\Logger
 */
class ContainerConfig extends AbstractConfig
{
    protected $name = 'container';

    protected $config = array(
        'container' => array(
            'aware' => array(
                'Bun\\Logger\\LoggerAwareInterface' => array(
                    'setLogger' => '@bun.logger'
                ),
            ),
        ),
        'bun.logger' => array(
            'class' => 'Bun\\Logger\\BunLogger',
        ),
        'bun.logger.event_listener.request' => array(
            'class' => 'Bun\\Logger\\EventListener\\RequestEventListener'
        ),
        'bun.logger.event_listener.shutdown' => array(
            'class' => 'Bun\\Logger\\EventListener\\ShutdownEventListener'
        ),
        'bun.logger.event_listener.response' => array(
            'class' => 'Bun\\Logger\\EventListener\\ResponseEventListener'
        )
    );
}