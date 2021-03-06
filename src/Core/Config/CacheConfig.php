<?php
namespace Bun\Core\Config;

/**
 * Class CacheConfig
 *
 * @package Bun\Core\Cache
 */
class CacheConfig extends AbstractConfig
{
    protected $name = 'cache';

    protected $config = array(
        'file' => array(
            'dir' => 'file_cache',
            'ttl' => 3600,
        ),
    );
}