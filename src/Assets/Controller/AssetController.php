<?php
namespace Bun\Assets\Controller;

use Bun\Core\Controller\AbstractController;
use Bun\Core\Exception\NotFoundException;
use Bun\Core\Http\Response;
use Bun\Core\Config\ApplicationConfig;
use Bun\Assets\AssetManager;

/**
 * Class AssetController
 *
 * @package Bun\Asset\Controller
 */
class AssetController extends AbstractController
{
    /**
     * @return Response
     * @throws \Bun\Core\Exception\NotFoundException
     */
    protected function cssAction()
    {
        list($path, $file, $alias) = $this->getAssetPath();
        $content = $this->getAssetManager()->css($path, $file, $alias);
        if ($content !== null) {
            $header = AssetManager::getHeaderByFileName($file);

            return new Response($content, array($header));
        }

        throw new NotFoundException('Requested css file could not be found: ' . $path . '/' . $file);
    }

    /**
     * @return Response
     * @throws \Bun\Core\Exception\NotFoundException
     */
    protected function jsAction()
    {
        list($path, $file) = $this->getAssetPath();
        $content = $this->getAssetManager()->js($path, $file);
        if ($content !== null) {
            $header = AssetManager::getHeaderByFileName($file);

            return new Response($content, array($header));
        }

        throw new NotFoundException('Requested javascript file could not be found: ' . $path . '/' . $file);
    }

    /**
     * @return Response
     * @throws \Bun\Core\Exception\NotFoundException
     */
    protected function imgAction()
    {
        list($path, $file) = $this->getAssetPath();
        $content = $this->getAssetManager()->image($path, $file);
        if ($content !== null) {
            $header = AssetManager::getHeaderByFileName($file);

            return new Response($content, array($header));
        }

        throw new NotFoundException('Requested image could not be found: ' . $path . '/' . $file);
    }

    /**
     * @return Response
     * @throws \Bun\Core\Exception\NotFoundException
     */
    protected function fileAction()
    {
        list($path, $file) = $this->getAssetPath();
        $content = $this->getAssetManager()->file($path, $file);
        if ($content !== null) {
            $header = AssetManager::getHeaderByFileName($file);

            return new Response($content, array($header));
        }

        throw new NotFoundException('Requested file could not be found: ' . $path . '/' . $file);
    }

    /**
     * @return Response
     * @throws \Bun\Core\Exception\NotFoundException
     */
    protected function fontAction()
    {
        list($path, $file) = $this->getAssetPath();
        $content = $this->getAssetManager()->file($path, $file);
        if ($content !== null) {
            $header = AssetManager::getHeaderByFileName($file);

            return new Response($content, array($header));
        }

        throw new NotFoundException('Requested font could not be found: ' . $path . '/' . $file);
    }

    /**
     * @return array
     * @throws \Bun\Core\Exception\NotFoundException
     */
    protected function getAssetPath()
    {
        $request = $this->getRequest();
        /** @var ApplicationConfig $applicationConfig */
        $applicationConfig = $this->container->get('bun.core.config');

        $path = $request->uri();
        $pathParts = array_values(array_filter(explode('/', $path)));
        $pathPartsCount = count($pathParts);
        $pathFile = end($pathParts);
        $publicPath = false;
        $publicAlias = false;
        $publics = array(PUBLIC_DIR);
        $configPublics = $this->getConfig()->get('asset.public');
        $assetAliases = $this->getConfig()->get('asset.asset_alias');
        if(!is_array($assetAliases)) {
            $assetAliases = array();
        }
        if($configPublics) {
            foreach($configPublics as $publicDir) {
                $publics[] = $publicDir;
            }
        }
        foreach($publics as $dir) {
            if(strpos($dir, '/' . $pathParts[0]) !== false) {
                $publicPath = true;
                if($dir !== PUBLIC_DIR) {
                    $publicAlias = $dir;
                }
                break;
            }
        }

        if (isset($pathParts[0]) && $publicPath) {
            if (isset($pathParts[1])) {
                if ($pathParts[1] === '_bun') {
                    // getting asset for bun module
                    if (isset($pathParts[2]) && in_array($pathParts[2], $applicationConfig->getBunModules())) {
                        if (isset($pathParts[3])) {
                            $assetDir = LIB_DIR . DIRECTORY_SEPARATOR . BUN_DIR . DIRECTORY_SEPARATOR . $pathParts[2] .
                                DIRECTORY_SEPARATOR . AssetManager::ASSET_DIR . DIRECTORY_SEPARATOR .
                                $pathParts[3];
                            for ($i = $pathPartsCount - 2; $i > 3; $i--) {
                                $pathFile = $pathParts[$i] . DIRECTORY_SEPARATOR . $pathFile;
                            }

                            return array($assetDir, $pathFile, $publicAlias);
                        }
                    }
                }
                elseif ($pathParts[1] === $this->application->getApplicationName() || in_array($pathParts[1], $assetAliases)) {
                    // getting asset for current application
                    if (isset($pathParts[2])) {
                        $assetDir = $this->application->getApplicationDir() .
                            DIRECTORY_SEPARATOR . AssetManager::ASSET_DIR . DIRECTORY_SEPARATOR .
                            $pathParts[2];
                        for ($i = $pathPartsCount - 2; $i > 2; $i--) {
                            $pathFile = $pathParts[$i] . DIRECTORY_SEPARATOR . $pathFile;
                        }

                        return array($assetDir, $pathFile, $publicAlias);
                    }
                }
                elseif ($this->config->getApplication($pathParts[1]) !== null) {
                    if (isset($pathParts[2])) {
                        $app = $this->config->getApplication($pathParts[1]);
                        $assetDir = $app->getApplicationDir() .
                            DIRECTORY_SEPARATOR . AssetManager::ASSET_DIR . DIRECTORY_SEPARATOR .
                            $pathParts[2];
                        for ($i = $pathPartsCount - 2; $i > 2; $i--) {
                            $pathFile = $pathParts[$i] . DIRECTORY_SEPARATOR . $pathFile;
                        }

                        return array($assetDir, $pathFile, $publicAlias);
                    }
                }
            }
        }

        throw new NotFoundException('Requested asset could not be found: ' . $path);
    }

}