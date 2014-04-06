<?php
namespace Bun\Assets;

use Bun\Core\ApplicationInterface;
use Bun\Core\Config\ConfigAwareInterface;
use Bun\Core\Config\ConfigInterface;
use Bun\Core\File\Directory;
use Bun\Core\File\File;

/**
 * Class AssetManager
 *
 * @package Bun\Asset
 */
class AssetManager implements ConfigAwareInterface
{
    const ASSET_DIR = 'Asset';
    /** @var ConfigInterface */
    protected $config;

    protected $cssConfig;
    protected $jsConfig;
    protected $imgConfig;

    protected $devMode = false;

    public static $extensionHeaders = array(
        'jpg'  => 'Content-type: image/jpeg',
        'jpeg' => 'Content-type: image/jpeg',
        'png'  => 'Content-type: image/png',
        'gif'  => 'Content-type: image/gif',
        'txt'  => 'Content-type: text/plain',
        'css'  => 'Content-type: text/css',
        'js'   => 'Content-type: text/javascript',
        'doc'  => 'Content-type: application/msword',
        'docx' => 'Content-type: application/msword',
        'xls'  => 'Content-type: application/vnd.ms-excel',
        'xlsx' => 'Content-type: application/vnd.ms-excel',
        'ppt'  => 'Content-type: application/vnd.ms-powerpoint',
        'pptx' => 'Content-type: application/vnd.ms-powerpoint',
        'pdf'  => 'Content-type: application/pdf',
        'zip'  => 'Content-type: application/zip',
        'exe'  => 'Content-type: application/octet-stream',
        'odt'  => 'Content-type: application/vnd.oasis.opendocument.text',
        'woff' => 'Content-type: application/x-font-woff',
    );

    public static $defaultHeader = 'application/force-download';

    /**
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        $this->devMode = $config->get('env') === ApplicationInterface::APPLICATION_ENV_DEV;
        $this->init();
    }

    /**
     * @param $fileName
     * @return string
     */
    public static function getHeaderByFileName($fileName)
    {
        $fileNameParts = explode('.', $fileName);
        $ext = end($fileNameParts);

        return isset(self::$extensionHeaders[$ext]) ?
            self::$extensionHeaders[$ext] :
            self::$defaultHeader;
    }


    /**
     * Initialize asset config
     */
    protected function init()
    {
        $assetConfig = $this->config->get('asset');
        $this->cssConfig = $assetConfig['css'];
        $this->jsConfig = $assetConfig['js'];
        $this->imgConfig = $assetConfig['img'];
    }

    /**
     * @param $path
     * @param $fileName
     * @param bool $publicAlias
     * @return string
     */
    protected function getAssetPublicFileName($path, $fileName, $publicAlias = false)
    {
        if (strpos($path, LIB_DIR) !== false) {
            // bun module asset
            $relPath = str_replace(LIB_DIR, '', $path);
            $relPath = str_replace('Bun', '_bun', $relPath);
        }
        else {
            $relPath = str_replace(APP_DIR, '', $path);
        }
        $relPath = str_replace(self::ASSET_DIR . DIRECTORY_SEPARATOR, '', $relPath);

        $public = ($publicAlias) ? str_replace(PUBLIC_PATH, '/' . $publicAlias, PUBLIC_DIR) : PUBLIC_DIR;

        return $public . $relPath . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * @param $assetFileName
     * @param $newFileName
     * @return bool
     */
    protected function installAssetFile($assetFileName, $newFileName)
    {
        if (!$this->devMode) {
            $newFileDir = dirname($newFileName);
            if (!is_dir($newFileDir)) {
                mkdir($newFileDir, 0777, true);
            }

            return File::copy($assetFileName, $newFileName);
        }

        return true;
    }

    /**
     * TODO: image filters
     *
     * @param $path
     * @param $fileName
     * @return null|string
     */
    public function image($path, $fileName)
    {
        $fullFileName = $path . DIRECTORY_SEPARATOR . $fileName;
        if (File::exists($fullFileName)) {
            $newFileName = $this->getAssetPublicFileName($path, $fileName);
            $this->installAssetFile($fullFileName, $newFileName);
            $assetFile = new File($fullFileName);

            return $assetFile->getContent();
        }

        return null;
    }

    /**
     * @param $path
     * @param $fileName
     * @param bool $publicAlias
     * @return int|null|string
     */
    public function css($path, $fileName, $publicAlias = false)
    {
        $fullFileName = $path . DIRECTORY_SEPARATOR . $fileName;
        if (isset($this->cssConfig[$fileName])) {
            return $this->installCssAssetConfig($fileName, $this->cssConfig[$fileName], $path, $publicAlias);
        }
        elseif (File::exists($fullFileName)) {
            $newFileName = $this->getAssetPublicFileName($path, $fileName);
            $this->installAssetFile($fullFileName, $newFileName);
            $assetFile = new File($fullFileName);

            return $assetFile->getContent();
        }

        return null;
    }

    /**
     * @param $assetName
     * @param $assetParams
     * @param $path
     * @param bool $publicAlias
     * @return int|string
     */
    protected function installCssAssetConfig($assetName, $assetParams, $path, $publicAlias = false)
    {
        if (isset($assetParams['files'])) {
            $files = array();
            foreach ($assetParams['files'] as $file) {
                $files[] = $path . DIRECTORY_SEPARATOR . $file;
            }
            $newFileName = $this->getAssetPublicFileName($path, $assetName, $publicAlias);
            if (isset($assetParams['less']) && $assetParams['less']) {
                require_once BUN_DIR . '/../lib/Less/lessc.inc.php';
                $parseFile = $files[0];
                $lessc = new \lessc();
                $lessc->setImportDir(dirname($parseFile));
                if (!$this->devMode) {
                    $f = new File($newFileName, true);
                    $lessc->compileFile($parseFile, $newFileName);

                    return $f->getContent(true);
                }
                else {
                    return $lessc->compileFile($parseFile);
                }
            }
            else {
                $content = '';
                foreach ($files as $file) {
                    if (File::exists($file)) {
                        $f = new File($file);
                        $content .= $f->getContent();
                    }
                }

                if (!$this->devMode) {
                    $install = new File($newFileName, true);
                    $install->setContent($content, true);
                }

                return $content;
            }
        }

        return '';
    }

    protected function installJsAssetConfig($assetName, $assetParams)
    {
        // TODO установка и минификация js

        return '';
    }

    /**
     * @param $path
     * @param $fileName
     * @return null|string
     */
    public function js($path, $fileName)
    {
        $fullFileName = $path . DIRECTORY_SEPARATOR . $fileName;
        if (isset($this->jsConfig[$fileName])) {
            return $this->installJsAssetConfig($fileName, $this->jsConfig[$fileName]);
        }
        elseif (File::exists($fullFileName)) {
            $newFileName = $this->getAssetPublicFileName($path, $fileName);
            $this->installAssetFile($fullFileName, $newFileName);
            $assetFile = new File($fullFileName);

            return $assetFile->getContent();
        }

        return null;
    }

    /**
     * @param $path
     * @param $fileName
     * @return null|string
     */
    public function file($path, $fileName)
    {
        $fullFileName = $path . DIRECTORY_SEPARATOR . $fileName;
        if (File::exists($fullFileName)) {
            $newFileName = $this->getAssetPublicFileName($path, $fileName);
            $this->installAssetFile($fullFileName, $newFileName);
            $assetFile = new File($fullFileName);

            return $assetFile->getContent();
        }

        return null;
    }

    /**
     * Gets link for asset
     *
     * @param $file
     * @return string
     */
    public function asset($file)
    {
        list($pubBasePath, $fileName) = $this->getAssetPathParts($file);

        return PUBLIC_PATH . DIRECTORY_SEPARATOR . $pubBasePath . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * @param $file
     * @return string
     */
    public function assetJs($file)
    {
        list($pubBasePath, $fileName) = $this->getAssetPathParts($file);
        if (ENV === ApplicationInterface::APPLICATION_ENV_DEV) {
            return $this->devAssetJs($file, $pubBasePath, $fileName);
        }

        return '<script src="' . PUBLIC_PATH . DIRECTORY_SEPARATOR .
        $pubBasePath . DIRECTORY_SEPARATOR . $fileName . '"></script>';
    }

    /**
     * @param $file
     * @param $pubBasePath
     * @param $fileName
     * @return string
     */
    public function devAssetJs($file, $pubBasePath, $fileName)
    {
        if (isset($this->jsConfig[$file]['files'])) {
            $html = '';
            $basePath = PUBLIC_PATH . DIRECTORY_SEPARATOR . $pubBasePath . DIRECTORY_SEPARATOR;
            foreach ($this->jsConfig[$file]['files'] as $assetFile) {
                $html .= '<script src="' . $basePath . DIRECTORY_SEPARATOR . $assetFile . '"></script>';
            }

            return $html;
        }

        return '<script src="' . PUBLIC_PATH . DIRECTORY_SEPARATOR .
        $pubBasePath . DIRECTORY_SEPARATOR . $fileName . '"></script>';
    }

    /**
     * @param $file
     * @return string
     */
    public function assetCss($file)
    {
        list($pubBasePath, $fileName) = $this->getAssetPathParts($file);
        if (ENV === ApplicationInterface::APPLICATION_ENV_DEV) {
            return $this->devAssetCss($file, $pubBasePath, $fileName);
        }

        return '<link rel="stylesheet" href="' . PUBLIC_PATH . DIRECTORY_SEPARATOR .
        $pubBasePath . DIRECTORY_SEPARATOR . $fileName . '">';
    }

    /**
     * @param $file
     * @param $pubBasePath
     * @param $fileName
     * @return string
     */
    public function devAssetCss($file, $pubBasePath, $fileName)
    {
        if (isset($this->cssConfig[$file]['files'])) {
            $html = '';
            $basePath = PUBLIC_PATH . DIRECTORY_SEPARATOR . $pubBasePath . DIRECTORY_SEPARATOR;
            foreach ($this->cssConfig[$file]['files'] as $assetFile) {
                $html .= '<link rel="stylesheet" href="' . $basePath . DIRECTORY_SEPARATOR . $assetFile . '">';
            }

            return $html;
        }

        return '<link rel="stylesheet" href="' . PUBLIC_PATH . DIRECTORY_SEPARATOR .
        $pubBasePath . DIRECTORY_SEPARATOR . $fileName . '">';
    }

    /**
     * @param $file
     * @return array
     */
    protected function getAssetPathParts($file)
    {
        $fileParts = explode('/', $file);
        $module = $fileParts[0];
        array_shift($fileParts);
        $fileName = join('/', $fileParts);
        if (strpos($module, 'bun.')) {
            $pubBasePath = '_bun' . DIRECTORY_SEPARATOR . str_replace('bun.', '', ucfirst($module));
        }
        else {
            $pubBasePath = ucfirst($module);
        }

        return array($pubBasePath, $fileName);
    }

    /**
     * Installs all assets
     *
     * @return array
     */
    public function install()
    {
        $this->devMode = false;
        $bunInstalled = $this->installBunAssets();
        $applicationsInstalled = $this->installApplicationsAssets();
        $configInstalled = $this->installConfigAssets();

        return array(
            'bun'    => $bunInstalled,
            'app'    => $applicationsInstalled,
            'config' => $configInstalled,
        );
    }

    /**
     * @param $moduleName
     * @param bool $isBunModule
     */
    public function clearInstalledModuleAssets($moduleName, $isBunModule = true)
    {
        $basePubDir = $isBunModule ? DIRECTORY_SEPARATOR . '_bun' : '';
        $installationDir = PUBLIC_DIR . $basePubDir . DIRECTORY_SEPARATOR . $moduleName;
        Directory::removeRecursive($installationDir);
    }

    /**
     * Clear installed assets
     */
    public function clear()
    {
        $bunBaseDir = LIB_DIR . DIRECTORY_SEPARATOR . 'Bun';
        $dirHandler = opendir($bunBaseDir);
        while ($bunModule = readdir($dirHandler)) {
            if ($bunModule !== '.' && $bunModule !== '..' && is_dir($bunBaseDir . DIRECTORY_SEPARATOR . $bunModule)) {
                $this->clearInstalledModuleAssets($bunModule);
            }
        }

        $appBaseDir = SRC_DIR;
        $dirHandler = opendir($appBaseDir);
        while ($bunApp = readdir($dirHandler)) {
            if ($bunApp !== '.' && $bunApp !== '..' && is_dir($appBaseDir . DIRECTORY_SEPARATOR . $bunApp)) {
                $this->clearInstalledModuleAssets($bunApp, false);
            }
        }
    }

    /**
     * Install assets from configs
     *
     * @return $this
     */
    protected function installConfigAssets()
    {
        $installed = array(
            'css' => 0,
            'js'  => 0,
        );

        foreach ($this->cssConfig as $assetName => $assetConfig) {
            //TODO: допилить тут
            //$this->installCssAssetConfig($assetName, $assetConfig);
            //$installed['css'] += 1;
        }
        foreach ($this->jsConfig as $assetName => $assetConfig) {
            //$this->installJsAssetConfig($assetName, $assetConfig);
            //$installed['js'] += 1;
        }

        return $installed;
    }

    /**
     * @return array
     */
    public function installBunAssets()
    {
        $bunBaseDir = LIB_DIR . DIRECTORY_SEPARATOR . 'Bun';
        $dirHandler = opendir($bunBaseDir);
        $installed = array();
        while ($bunModule = readdir($dirHandler)) {
            if ($bunModule !== '.' && $bunModule !== '..' && is_dir($bunBaseDir . DIRECTORY_SEPARATOR . $bunModule)) {
                $installed[$bunModule] = $this->installModuleAssets(
                    $bunBaseDir . DIRECTORY_SEPARATOR . $bunModule,
                    $bunModule
                );
            }
        }

        return $installed;
    }

    /**
     * @param $moduleDir
     * @param $moduleName
     * @param bool $isBunModule
     * @return int
     */
    protected function installModuleAssets($moduleDir, $moduleName, $isBunModule = true)
    {
        $this->clearInstalledModuleAssets($moduleName, $isBunModule);
        $assetsInstalled = 0;
        $basePublic = $isBunModule ? DIRECTORY_SEPARATOR . '_bun' : '';
        $assetsDir = $moduleDir . DIRECTORY_SEPARATOR . self::ASSET_DIR;
        if (is_dir($assetsDir)) {
            $dirHandler = opendir($assetsDir);
            while ($dir = readdir($dirHandler)) {
                if ($dir !== '.' && $dir !== '..' && is_dir($assetsDir . DIRECTORY_SEPARATOR . $dir)) {
                    $baseNewPath = PUBLIC_DIR . $basePublic . DIRECTORY_SEPARATOR .
                        $moduleName . DIRECTORY_SEPARATOR . $dir;
                    $assetsInstalled += $this->recursiveInstallAssetFromDir(
                        $assetsDir . DIRECTORY_SEPARATOR . $dir,
                        $baseNewPath
                    );
                }
            }
        }

        return $assetsInstalled;
    }

    /**
     * @param $dir
     * @param $baseNewPath
     * @return int
     */
    protected function recursiveInstallAssetFromDir($dir, $baseNewPath)
    {
        $installed = 0;
        $dirHandler = opendir($dir);
        while ($assetFile = readdir($dirHandler)) {
            if ($assetFile !== '.' && $assetFile !== '..' && is_dir($dir . DIRECTORY_SEPARATOR . $assetFile)) {
                $installed += $this->recursiveInstallAssetFromDir($dir . DIRECTORY_SEPARATOR . $assetFile, $baseNewPath);
            }
            elseif (is_file($dir . DIRECTORY_SEPARATOR . $assetFile)) {
                $newFileName = $baseNewPath . DIRECTORY_SEPARATOR . $assetFile;
                $this->installAssetFile($dir . DIRECTORY_SEPARATOR . $assetFile, $newFileName);
                $installed++;
            }
        }

        return $installed;
    }

    /**
     * @return array
     */
    public function installApplicationsAssets()
    {
        $appBaseDir = SRC_DIR;
        $dirHandler = opendir($appBaseDir);
        $installed = array();
        while ($bunModule = readdir($dirHandler)) {
            if ($bunModule !== '.' && $bunModule !== '..' && is_dir($appBaseDir . DIRECTORY_SEPARATOR . $bunModule)) {
                $installed[$bunModule] = $this->installModuleAssets(
                    $appBaseDir . DIRECTORY_SEPARATOR . $bunModule,
                    $bunModule,
                    false
                );
            }
        }

        return $installed;
    }
}