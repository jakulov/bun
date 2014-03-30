<?php
namespace Bun\Core\Config;

use Bun\Core\Application;
use Bun\Core\ApplicationInterface;
use Bun\Core\File\File;

/**
 * Class ApplicationConfig
 *
 * @package Bun\Core\Config
 */
class ApplicationConfig extends AbstractConfig
{
    const CONFIG_CACHE_DIR = 'cache/config';
    /** @var \Bun\Core\Application */
    protected $application;
    protected $config = array();
    /** @var array */
    protected $bunModules = array();
    /** @var ApplicationInterface[] */
    protected $bunApplications = array();

    /**
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $cacheConfig = $this->getConfigFromCache($application->getApplicationName());
        if (
            $cacheConfig !== null &&
            $application->getEnvironment() !== ApplicationInterface::APPLICATION_ENV_DEV
        ) {
            $this->config = $cacheConfig;
            $this->bunModules = $cacheConfig['__modules'];
            $this->bunApplications = $cacheConfig['__applications'];
        }
        else {
            $this->application = $application;

            $this->initBunConfig();
            $this->initApplicationConfig();
            $this->config['env'] = $application->getEnvironment();

            $this->saveConfigCache($application->getApplicationName());
        }
    }

    /**
     * @return array
     */
    public function getBunModules()
    {
        return $this->bunModules;
    }

    /**
     * @param $appName
     * @return ApplicationInterface|null
     */
    public function getApplication($appName)
    {
        if(isset($this->bunApplications[$appName])) {
            return $this->bunApplications[$appName];
        }

        return null;
    }

    /**
     * @return \Bun\Core\ApplicationInterface[]
     */
    public function getApplicationsList()
    {
        return $this->bunApplications;
    }

    /**
     * @param ConfigInterface $moduleConfig
     */
    protected function loadModuleConfig(ConfigInterface $moduleConfig)
    {
        $name = $moduleConfig->getName();
        $config = $moduleConfig->getConfig($this->application->getEnvironment());
        if (!empty($name)) {
            if (!isset($this->config[$name])) {
                $this->config[$name] = array();
            }
            $this->config[$name] = array_replace_recursive($this->config[$name], $config);
        }
        else {
            foreach ($config as $key => $value) {
                if (isset($this->config[$key]) && is_array($this->config[$key]) && is_array($value)) {
                    $this->config[$key] = array_replace_recursive($this->config[$key], $value);
                }
                else {
                    $this->config[$key] = $value;
                }
            }
        }
    }

    /**
     * Initialize app config
     */
    protected function initApplicationConfig()
    {
        $configDir = $this->application->getApplicationDir() . DIRECTORY_SEPARATOR . self::CONFIG_NAMESPACE;
        // loading other applications config first
        $this->initApplicationsList();
        // load this application config second
        $this->loadApplicationConfig($this->application, $configDir);
    }

    /**
     * @param ApplicationInterface $application
     * @param $configDir
     * @param null $ignore
     */
    protected function loadApplicationConfig(ApplicationInterface $application, $configDir, $ignore = null)
    {
        $dirHandler = opendir($configDir);
        while ($appConfig = readdir($dirHandler)) {
            if ($appConfig !== '.' && $appConfig !== '..' && is_file($configDir . DIRECTORY_SEPARATOR . $appConfig)) {
                $appConfigClass =
                    $application->getApplicationName() . '\\' . self::CONFIG_NAMESPACE . '\\' .
                    str_replace('.php', '', $appConfig);
                if (class_exists($appConfigClass)) {
                    /** @var \Bun\Core\Config\AbstractConfig $appConfigItem */
                    $appConfigItem = new $appConfigClass();
                    if($ignore !== null && $appConfigItem->getName() === $ignore) {
                        continue;
                    }
                    $this->loadModuleConfig($appConfigItem);
                }
            }
        }
    }

    /**
     * Initialize bun applications list
     */
    protected function initApplicationsList()
    {
        $appDirHandler = opendir(SRC_DIR);
        while ($appDir = readdir($appDirHandler)) {
            if ($appDir !== '.' && $appDir !== '..' && is_dir(SRC_DIR . DIRECTORY_SEPARATOR . $appDir)) {
                $appFile = SRC_DIR . DIRECTORY_SEPARATOR . $appDir . DIRECTORY_SEPARATOR . 'Application.php';
                if (file_exists($appFile)) {
                    require_once $appFile;
                    $appClass = $appDir . '\\Application';
                    if (class_exists($appClass)) {
                        /** @var ApplicationInterface $app */
                        $app = new $appClass;
                        $this->bunApplications[$app->getApplicationName()] = $app;
                        $this->loadOtherApplicationConfig($app, SRC_DIR . DIRECTORY_SEPARATOR . $appDir);
                    }
                }
            }
        }
    }

    /**
     * Loads other application configs
     *
     * @param ApplicationInterface $application
     * @param $appDir
     */
    protected function loadOtherApplicationConfig(ApplicationInterface $application, $appDir)
    {
        $configDir = $appDir . DIRECTORY_SEPARATOR . self::CONFIG_NAMESPACE;
        if (is_dir($configDir)) {
            $this->loadApplicationConfig($application, $configDir, 'router');
        }
    }

    /**
     * @param $applicationName
     * @return bool|int|null
     */
    protected function saveConfigCache($applicationName)
    {
        $configFile = $this->getConfigCacheFileName($applicationName);
        $file = new File($configFile, true);

        $this->config['__modules'] = $this->bunModules;
        $this->config['__applications'] = $this->bunApplications;

        return $file->setContent(serialize($this->config), true);
    }

    /**
     * @param $applicationName
     * @return array|null
     */
    protected function getConfigFromCache($applicationName)
    {
        $configFile = $this->getConfigCacheFileName($applicationName);
        if (File::exists($configFile)) {
            $file = new File($configFile, false);
            $data = $file->getContent();
            if ($data) {
                return unserialize($data);
            }
        }

        return null;
    }

    /**
     * @param $applicationName
     * @return string
     */
    protected function getConfigCacheFileName($applicationName)
    {
        $configCacheDir = $configCacheFile = VAR_DIR . DIRECTORY_SEPARATOR . self::CONFIG_CACHE_DIR . DIRECTORY_SEPARATOR .
            $applicationName;
        if (!is_dir($configCacheDir)) {
            mkdir($configCacheDir, 0777, true);
        }

        return $configCacheDir . DIRECTORY_SEPARATOR . 'config.cache';
    }

    /**
     * @throws ConfigException
     */
    protected function initBunConfig()
    {
        $bunDir = __DIR__ . '/../..';
        $dirHandler = opendir($bunDir);
        while ($bunModule = readdir($dirHandler)) {
            if ($bunModule !== '.' && $bunModule !== '..' && is_dir($bunDir . DIRECTORY_SEPARATOR . $bunModule)) {
                $moduleClass = self::BUN_NAMESPACE . '\\' . $bunModule . '\\' . $bunModule;
                if (class_exists($moduleClass)) {
                    $this->bunModules[] = $bunModule;
                    /** @var \Bun\Core\Module\AbstractModule $module */
                    $module = new $moduleClass();
                    $moduleConfig = $module->getConfig();
                    foreach ($moduleConfig as $configItem) {
                        $configClass =
                            self::BUN_NAMESPACE . '\\' . $bunModule . '\\' .
                            self::CONFIG_NAMESPACE . '\\' . $configItem . self::CONFIG_NAMESPACE;
                        if (class_exists($configClass)) {
                            /** @var \Bun\Core\Config\ConfigInterface $moduleConfigItem */
                            $moduleConfigItem = new $configClass();
                            if ($moduleConfigItem instanceof ConfigInterface) {
                                $this->loadModuleConfig($moduleConfigItem);
                            }
                            else {
                                throw new ConfigException(
                                    'Module ' . $moduleClass . ' ' . $configClass . ' config class should implements ' .
                                    ' Bun\\Core\\Config\\ConfigInterface'
                                );
                            }
                        }
                    }
                }
            }
        }
    }
}