<?php
namespace Bun\Core\Config;

/**
 * Class ToolConfig
 *
 * @package Bun\Core\Config
 */
class ToolConfig extends AbstractConfig
{
    protected $name = 'tool';

    protected $config = array(
        'bun.core.cache' => 'Bun\\Core\\Controller\\CacheController',
        'bun.core.model' => 'Bun\\Core\\Controller\\ModelController',
    );
}