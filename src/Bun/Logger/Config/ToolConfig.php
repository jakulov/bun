<?php
namespace Bun\Logger\Config;

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
        'bun.logger' => 'Bun\\Logger\\Tool\\LoggerController'
    );
}