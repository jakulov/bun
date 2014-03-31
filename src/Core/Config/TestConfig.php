<?php
namespace Bun\Core\Config;

/**
 * Class TestConfig
 *
 * @package Bun\Core\Config
 */
class TestConfig extends AbstractConfig
{
    protected $name = 'test';

    protected $config = array(
        'test1' => 'test1',
        'test2' => array(
            'test1' => 'test1',
        ),
        'test3' => ':test'
    );
}