<?php
namespace Bun\Form\Config;

use Bun\Core\Config\AbstractConfig;

/**
 * Class ContainerConfig
 *
 * @package Bun\Form\Config
 */
class ContainerConfig extends AbstractConfig
{
    protected $name = 'container';

    protected $config = array(
        'bun.form.builder' => array(
            'class' => 'Bun\\Form\\FormBuilder'
        )
    );
}