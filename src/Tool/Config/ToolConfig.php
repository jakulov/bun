<?php
namespace Bun\Tool\Config;

use Bun\Core\Config\AbstractConfig;

/**
 * Class ToolConfig
 *
 * @package Bun\Tool\Config
 */
class ToolConfig extends AbstractConfig
{
    protected $name = 'tool';

    protected $config = array(
        'bun.tool' => 'Bun\\Tool\\Controller\\ToolController'
    );
}