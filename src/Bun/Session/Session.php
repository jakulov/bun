<?php
namespace Bun\Session;

use Bun\Core\Module\AbstractModule;

/**
 * Class Session
 *
 * @package Bun\Session
 */
class Session extends AbstractModule
{
    protected $version = '0.1';
    protected $description = 'Bun Session module';
    protected $source = 'https://github.com/jakulov/bun_session';
    protected $sourceType = self::MODULE_SOURCE_TYPE_GITHUB;
    protected $sourceBranch = 'master';
    protected $dependencies = array(
        'bun',
    );
    protected $config = array(
        'Session',
        'Container',
        'Event',
    );
}