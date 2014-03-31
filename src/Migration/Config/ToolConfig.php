<?php
namespace Bun\Migration\Config;

/**
 * Class ToolConfig
 *
 * @package Bun\Migration\Config
 */
class ToolConfig extends \Bun\Tool\Config\ToolConfig
{
    protected $config = array(
        'bun.migration' => 'Bun\\Migration\\Controller\\MigrationController'
    );
}