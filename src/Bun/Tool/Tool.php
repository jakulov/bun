<?php
namespace Bun\Tool;

use Bun\Core\Module\AbstractModule;

/**
 * Class Tool
 *
 * @package Bun\Tool
 */
class Tool extends AbstractModule
{
    protected $version = '0.1';
    protected $description = 'Bun core tools';
    protected $source = 'https://github.com/jakulov/bun_tools';
    protected $sourceType = self::MODULE_SOURCE_TYPE_GITHUB;
    protected $sourceBranch = 'master';
    protected $dependencies = array(
        'bun'
    );
    protected $config = array(
        'Container',
        'Tool'
    );
}