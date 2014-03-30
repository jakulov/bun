<?php
namespace Bun\Core\Cache;

use Bun\Core\Config\AbstractConfig;

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