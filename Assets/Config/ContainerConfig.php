<?php
namespace Bun\Assets\Config;

use Bun\Core\Config\AbstractConfig;

/**
 * Class ContainerConfig
 *
 * @package Bun\Asset\Config
 */
class ContainerConfig extends AbstractConfig
{
    protected $name = 'container';

    protected $config = array(
        'bun.assets.manager' => array(
            'class' => 'Bun\\Assets\\AssetManager'
        ),
        'bun.assets.helper' => array(
            'class' => 'Bun\\Assets\\Helper\\AssetHelper',
            'aware' => array(
                'setAssetManager' => '@bun.assets.manager'
            ),
        ),
    );
}