<?php
namespace Bun\Core\Config;

/**
 * Class AbstractConfig
 *
 * @package Bun\Core\Config
 */
abstract class AbstractConfig implements ConfigInterface
{
    /** @var array */
    protected $config = array();
    /** @var string */
    protected $name = '';
    /** @var  string */
    protected $env;

    /**
     * Reloads config with params
     */
    public function __construct()
    {
        // TODO: add ignored config parameters
        $reloadConfig = array();
        foreach ($this->config as $param => $value) {
            if (is_array($value)) {
                $reloadConfig[$param] = $this->load($value);
            }
            /*elseif (strpos($value, ':') === 0) {
                $configParam = substr($value, 1);
                $reloadConfig[$param] = \FConfig::get($configParam);
            }*/
            else {
                $reloadConfig[$param] = $value;
            }
        }

        $this->config = $reloadConfig;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $param
     * @return mixed|null
     */
    public function get($param = null)
    {
        if ($param === null) {
            return $this->config;
        }

        if (strpos($param, '.') !== false) {
            $paramParts = explode('.', $param);

            return $this->recursiveGet($paramParts, $this->config);
        }

        return (isset($this->config[$param])) ?
            $this->config[$param] :
            null;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->env;
    }

    /**
     * @param $env
     * @return array
     */
    public function getConfig($env)
    {
        $this->env = $env;

        return $this->mergeEnvConfig();
    }

    /**
     * @param $config
     * @return array
     */
    protected function load($config)
    {
        // TODO: add ignored config parameters
        $reloadConfig = array();
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $reloadConfig[$key] = $this->load($value);
            }
            /*elseif (strpos($value, ':') === 0) {
                $configParam = substr($value, 1);
                $reloadConfig[$key] = \FConfig::get($configParam);
            }*/
            else {
                $reloadConfig[$key] = $value;
            }
        }

        return $reloadConfig;
    }

    /**
     * @return array
     */
    protected function mergeEnvConfig()
    {
        if (!($this instanceof EnvConfigInterface)) {
            $this->initEnvConfig();
            $envConfig = $this->env . '_config';
            if (property_exists($this, $envConfig)) {
                return array_replace_recursive($this->config, $this->$envConfig);
            }
        }

        return $this->config;
    }

    /**
     * Initialize environment config
     */
    protected function initEnvConfig()
    {
        $config = new \ReflectionClass(get_class($this));
        $configDir = $config->getFileName();
        $configNS = $config->getNamespaceName();
        $envConfigFile = dirname($configDir) . DIRECTORY_SEPARATOR . ucfirst($this->env) .
            DIRECTORY_SEPARATOR . ucfirst($this->getName()) . 'Config.php';

        if (file_exists($envConfigFile)) {
            $envConfigClassName = $configNS . '\\' . ucfirst($this->env) . '\\' . ucfirst($this->name) . 'Config';
            require_once $envConfigFile;
            if (class_exists($envConfigClassName, false)) {
                $envConfig = new $envConfigClassName;
                if ($envConfig instanceof EnvConfigInterface) {
                    $envProperty = $this->env . '_config';
                    $this->$envProperty = $envConfig->getConfig($this->env);
                }
            }
        }
    }

    /**
     * @return string
     */
    protected function getDir()
    {
        return __DIR__;
    }

    /**
     * @param array $paramParts
     * @param array $config
     * @return mixed|null
     */
    protected function recursiveGet($paramParts = array(), $config = array())
    {
        $param = array_shift($paramParts);
        if (isset($config[$param])) {
            return (!$paramParts) ?
                $config[$param] :
                $this->recursiveGet($paramParts, $config[$param]);
        }

        return null;
    }
}