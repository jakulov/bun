<?php
namespace Bun\Migration;

use Bun\Core\Module\AbstractModule;

/**
 * Class Migration
 *
 * @package Bun\Migration
 */
class Migration extends AbstractModule
{
    protected $version = '0.1';
    protected $description = 'Bun Migration module';
    protected $source = 'https://github.com/jakulov/bun_migration';
    protected $sourceType = self::MODULE_SOURCE_TYPE_GITHUB;
    protected $sourceBranch = 'master';
    protected $dependencies = array(
        'bun',
        'PDO'
    );

    protected $config = array(
        'Tool',
        'Container',
        'Migration',
    );
}