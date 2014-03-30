<?php
namespace Bun\PDO\Config;

use Bun\Core\Config\AbstractConfig;

/**
 * Class PdoConfig
 *
 * @package Bun\PDO\Config
 */
class PdoConfig extends AbstractConfig
{
    protected $name = 'pdo';

    protected $config = array(
        'pdo' => array(
            'dns'      => 'mysql:127.0.0.1:3306',
            'username' => 'root',
            'password' => '',
            'options'  => array(),
        )
    );
}