<?php
namespace Bun\Cache\Config;

use Bun\Core\Config\AbstractConfig;

/**
 * Class CacheConfig
 *
 * @package Bun\Cache
 */
class CacheConfig extends AbstractConfig
{
    protected $name = 'cache';

    protected $config = array(
        'memcache' => array(
            'host' => '127.0.0.1',
            'port' => '11211',
        ),
    );
}