<?php
namespace Bun\Session\Config;

use Bun\Core\Config\AbstractConfig;

/**
 * Class ContainerConfig
 *
 * @package Bun\Session\Config
 */
class ContainerConfig extends AbstractConfig
{
    protected $name = 'container';

    protected $config = array(
        'container'                           => array(
            'aware' => array(
                'Bun\\Session\\SessionAwareInterface'        => array(
                    'setSession' => '@bun.session'
                ),
                'Bun\\Session\\SessionStorageAwareInterface' => array(
                    'setSessionStorage' => '@bun.session.native_storage'
                ),
            ),
        ),
        'bun.session'                         => array(
            'class' => 'Bun\\Session\\BunSession'
        ),
        'bun.session.file_storage'            => array(
            'class' => 'Bun\\Session\\FileSessionStorage'
        ),
        'bun.session.native_storage'          => array(
            'class' => 'Bun\\Session\\NativeSessionStorage'
        ),
        'bun.session.event_listener.response' => array(
            'class' => 'Bun\\Session\\EventListener\\ResponseEventListener'
        ),
    );
}