<?php
namespace Bun\Assets\Helper;

use Bun\Assets\AssetManager;

/**
 * Class AssetHelper
 *
 * @package Bun\Assets\Helper
 */
class AssetHelper extends \Twig_Extension
{
    /** @var AssetManager */
    protected $assetManager;

    /**
     * @return string
     */
    public function getName()
    {
        return 'asset';
    }

    /**
     * @param AssetManager $assetManager
     */
    public function setAssetManager(AssetManager $assetManager)
    {
        $this->assetManager = $assetManager;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('asset_js', array($this, 'assetJs')),
            new \Twig_SimpleFunction('asset_css', array($this, 'assetCss')),
            new \Twig_SimpleFunction('asset_img', array($this, 'assetImg')),
        );
    }

    /**
     * @param $file
     * @return string
     */
    public function assetJs($file)
    {
        return $this->assetManager->assetJs($file);
    }

    /**
     * @param $file
     * @return string
     */
    public function assetCss($file)
    {
        return $this->assetManager->assetCss($file);
    }

    /**
     * @param $file
     * @return string
     */
    public function assetImg($file)
    {
        return $this->assetManager->asset($file);
    }
}