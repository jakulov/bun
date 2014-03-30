<?php
namespace Bun\Cache\Config;

use Bun\Core\Config\AbstractConfig;

/**
 * Class ContainerConfig
 *
 * @package Bun\Cache
 */
class ContainerConfig extends AbstractConfig
{
    protected $name = 'container';

    protected $config = array(
        'bun.cache.memcache' => array(
            'class' => 'Bun\\Cache\\MemcacheCacheDriver'
        )
    );
}