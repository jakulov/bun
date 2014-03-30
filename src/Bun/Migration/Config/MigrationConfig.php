<?php
namespace Bun\Migration;

use Bun\Core\Config\AbstractConfig;

/**
 * Class MigrationConfig
 *
 * @package Bun\Migration
 */
class MigrationConfig extends AbstractConfig
{
    protected $name = 'migration';

    protected $config = array(
        'model' => null,
    );
}