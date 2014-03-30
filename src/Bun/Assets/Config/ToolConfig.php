<?php
namespace Bun\Assets\Config;

use Bun\Core\Config\AbstractConfig;

/**
 * Class ToolConfig
 *
 * @package Bun\Assets\Config
 */
class ToolConfig extends AbstractConfig
{
    protected $name = 'tool';

    protected $config = array(
        'bun.assets' => 'Bun\\Assets\\Controller\\ToolController'
    );
}