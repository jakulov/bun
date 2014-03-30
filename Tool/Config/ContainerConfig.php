<?php
namespace Bun\Tool\Config;

use Bun\Core\Config\AbstractConfig;

/**
 * Class ContainerConfig
 *
 * @package Bun\Tool\Config
 */
class ContainerConfig extends AbstractConfig
{
    protected $name = 'container';

    protected $config = array(
        'bun.tool.manager' => array(
            'class' => '\\Bun\\Tool\\ToolManager'
        ),
    );
}