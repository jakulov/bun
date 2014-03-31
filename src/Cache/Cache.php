<?php
namespace Bun\Cache;

use Bun\Core\Module\AbstractModule;

/**
 * Class Cache
 *
 * @package Bun\Cache
 */
class Cache extends AbstractModule
{
    protected $version = '0.1';
    protected $description = 'Bun Cache module';
    protected $source = 'https://github.com/jakulov/bun_cache';
    protected $sourceType = self::MODULE_SOURCE_TYPE_GITHUB;
    protected $sourceBranch = 'master';
    protected $dependencies = array(
        'bun',
        'session',
    );
    protected $config = array(
        'Cache',
        'Container',
    );
}