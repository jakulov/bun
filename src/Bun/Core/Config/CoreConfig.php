<?php
namespace Bun\Core\Config;

/**
 * Class CoreConfig
 *
 * @package Bun\Core\Config
 */
class CoreConfig extends AbstractConfig
{
    protected $name = 'core';

    protected $config = array(
        'storage'       => array(
            'dir'            => 'file_storage',
            'cache_dir'      => 'file_storage_cache',
            'file_extension' => 'model',
            'cache_ttl'      => 36000,
        ),
        'object_mapper' => array(
            'storage'   => 'bun.core.file_storage',
        )
    );
}