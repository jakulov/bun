<?php
namespace Bun\Migration\Config;

use Bun\Core\Config\AbstractConfig;

/**
 * Class ContainerConfig
 *
 * @package Bun\Migration\Config
 */
class ContainerConfig extends AbstractConfig
{
    protected $name = 'container';

    protected $config = array(
        'bun.migration.manager' => array(
            'class' => 'Bun\\Migration\\MigrationManager',
        ),
    );
}