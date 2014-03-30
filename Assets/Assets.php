<?php
namespace Bun\Assets;

use Bun\Core\Module\AbstractModule;

/**
 * Class Asset
 *
 * @package Bun\Asset
 */
class Assets extends AbstractModule
{
    protected $version = '0.1';
    protected $description = 'Bun asset tools';
    protected $source = 'https://github.com/jakulov/bun_asset';
    protected $sourceType = self::MODULE_SOURCE_TYPE_GITHUB;
    protected $sourceBranch = 'master';
    protected $dependencies = array(
        'bun',
        'tool'
    );
    protected $config = array(
        'Router',
        'Container',
        'Asset',
        'Tool',
    );
}