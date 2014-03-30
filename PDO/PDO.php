<?php
namespace Bun\PDO;

use Bun\Core\Module\AbstractModule;

/**
 * Class MySQL
 *
 * @package MySQL
 */
class PDO extends AbstractModule
{
    protected $version = '0.1';
    protected $description = 'Bun PDO module';
    protected $source = 'https://github.com/jakulov/bun_pdo';
    protected $sourceType = self::MODULE_SOURCE_TYPE_GITHUB;
    protected $sourceBranch = 'master';
    protected $dependencies = array(
        'bun'
    );
    protected $config = array(
        'Pdo',
        'Container',
        'Event',
        'Logger',
    );
}