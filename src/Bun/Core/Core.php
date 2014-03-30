<?php
namespace Bun\Core;

/**
 * Class Core
 *
 * @package Bun\Core
 */
final class Core extends Module\AbstractModule
{
    const MODULE_REPOSITORY_API_URL = 'http://bundev.ru/api/module';

    protected $version = '0.1';
    protected $source = 'https://github.com/jakulov/bun';
    protected $sourceType = self::MODULE_SOURCE_TYPE_GITHUB;
    protected $sourceBranch = 'master';
    protected $description = 'Bun Framework core module';
    protected $dependencies = array();
    protected $config = array(
        'Core',
        'Container',
        'Cache',
        'Repository',
        'Router',
        'Event',
        'Tool',
        'Test',
    );
}