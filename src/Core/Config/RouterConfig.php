<?php
namespace Bun\Core\Config;

/**
 * Class RouterConfig
 *
 * @package Bun\Core\Config
 */
class RouterConfig extends AbstractConfig
{
    protected $name = 'router';

    protected $config = array(
        '/'            => array(
            'controller' => 'Bun\\Core\\Controller\\BunController'
        ),
        '/favicon.ico' => array(
            'controller' => 'Bun\\Core\\Controller\\BunController',
            'action'     => 'favicon'
        ),
        '/bun/phpinfo' => array(
            'controller' => 'Bun\\Core\\Controller\\BunController',
            'action'     => 'phpinfo',
        ),
    );
}