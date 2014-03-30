<?php
namespace Bun\Assets\Config;

use Bun\Core\Config\AbstractConfig;

/**
 * Class AssetConfig
 *
 * @package Bun\Asset\Config
 */
class AssetConfig extends AbstractConfig
{
    protected $name = 'asset';

    protected $config = array(
        'css' => array(
            'bun.assets/css/bun.min.css' => array(
                'files' => array (
                    'css/bun.css',
                    'css/error.css'
                ),
                'minify' => true,
            )
        ),
        'js' => array(),
        'img' => array(),
    );
}