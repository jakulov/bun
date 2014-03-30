<?php
namespace Bun\Assets\Config;

use Bun\Core\Config\AbstractConfig;

/**
 * Class RouterConfig
 *
 * @package Bun\Assets\Config
 */
class RouterConfig extends AbstractConfig
{
    protected $name = 'router';

    protected $config = array(
        '/public/.*(|:file)\.css' => array(
            'controller' => 'Bun\\Assets\\Controller\\AssetController',
            'action'     => 'css'
        ),
        '/public/.*(|:file)\.(jpg|jpeg|png|gif)' => array(
            'controller' => 'Bun\\Assets\\Controller\\AssetController',
            'action'     => 'img'
        ),
        '/public/.*(|:file)\.js' => array(
            'controller' => 'Bun\\Assets\\Controller\\AssetController',
            'action'     => 'img'
        ),
        '/public/.*(|:file)\.(txt|doc|docx|odt|pdf|xls|xlsx|tiff)' => array(
            'controller' => 'Bun\\Assets\\Controller\\AssetController',
            'action'     => 'file'
        ),
        '/public/.*(|:file)\.(ttf|svg|woff|eot)' => array(
            'controller' => 'Bun\\Assets\\Controller\\AssetController',
            'action'     => 'font'
        ),
    );
}