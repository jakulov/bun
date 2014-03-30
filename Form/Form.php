<?php
namespace Bun\Form;

use Bun\Core\Module\AbstractModule;

/**
 * Class Form
 *
 * @package Bun\Form
 */
class Form extends AbstractModule
{
    protected $version = '0.1';
    protected $description = 'Bun form tools';
    protected $source = 'https://github.com/jakulov/bun_form';
    protected $sourceType = self::MODULE_SOURCE_TYPE_GITHUB;
    protected $sourceBranch = 'master';
    protected $dependencies = array(
        'bun'
    );
    protected $config = array(
        'Container',
    );
}