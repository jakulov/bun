<?php
namespace Bun\Logger;

use Bun\Core\Module\AbstractModule;

/**
 * Class Logger
 *
 * @package Bun\Logger
 */
class Logger extends AbstractModule
{
    protected $version = '0.1';
    protected $description = 'Bun Logger module';
    protected $source = 'https://github.com/jakulov/bun_logger';
    protected $sourceType = self::MODULE_SOURCE_TYPE_GITHUB;
    protected $sourceBranch = 'master';
    protected $dependencies = array(
        'bun'
    );
    protected $config = array(
        'Logger',
        'Container',
        'Event',
        'Tool',
    );
}