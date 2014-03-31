<?php
namespace Bun\Core\Config;

/**
 * Class RepositoryConfig
 *
 * @package Bun\Core\Config
 */
class RepositoryConfig extends AbstractConfig
{
    protected $name = 'repository';

    protected $config = array(
        'Bun\\Core\\Model\\User' => 'Bun\\Core\\Repository\\UserRepository',
    );
}