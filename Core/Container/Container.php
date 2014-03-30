<?php
namespace Bun\Core\Container;

use Bun\Core\ApplicationInterface;
use Bun\Core\Config\ConfigInterface;

/**
 * Class Container
 *
 * @package Bun\Core\Container
 */
class Container implements ContainerInterface
{
    /** @var Container */
    protected static $instance;
    /** @var \Bun\Core\Config\ConfigInterface */
    protected $config;
    /** @var array */
    protected $containerConfig = array();
    /** @var array Cached services */
    protected $services = array();
    /** @var array  */
    protected $dependencies = array();

    /**
     * @param ConfigInterface $config
     */
    protected function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $this->init();
    }

    /**
     * @param $serviceName
     * @param bool $asDependency
     * @return mixed
     */
    public function get($serviceName, $asDependency = false)
    {
        if (isset($this->services[$serviceName])) {
            return $this->services[$serviceName];
        }

        $this->dependencies = array($serviceName);

        return $this->initService($serviceName);
    }

    /**
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Init config
     */
    protected function init()
    {
        $this->containerConfig = $this->config->get('container');
        $this->services['bun.core.config'] = $this->config;
        $this->services['bun.core.container'] = $this;
    }

    /**
     * @param ConfigInterface $config
     * @return Container
     */
    public static function getInstance(ConfigInterface $config)
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        self::$instance = new self($config);

        return self::$instance;
    }

    /**
     * @param $serviceName
     * @return mixed
     * @throws ContainerException
     */
    protected function initService($serviceName)
    {
        if (isset($this->containerConfig[$serviceName])) {
            $serviceClass = $this->containerConfig[$serviceName]['class'];
            $serviceAware = isset($this->containerConfig[$serviceName]['aware']) ?
                $this->containerConfig[$serviceName]['aware'] :
                null;
            if (class_exists($serviceClass)) {
                $service = new $serviceClass();
                $aware = $serviceAware !== null ?
                    array_replace_recursive($this->getServiceInterfaces($service), $serviceAware) :
                    $this->getServiceInterfaces($service);
                foreach ($aware as $method => $param) {
                    call_user_func_array(
                        array($service, $method),
                        array($this->getServiceParam($param))
                    );
                }

                $this->services[$serviceName] = $service;

                return $service;
            }
        }

        throw new ContainerException('Unable to find service: ' . $serviceName);
    }

    /**
     * @param $serviceName
     * @return mixed
     * @throws ContainerException
     */
    protected function getDependency($serviceName)
    {
        if (!in_array($serviceName, $this->dependencies)) {
            $this->dependencies[] = $serviceName;
        }
        else {
            throw new ContainerException('Service ' . $this->dependencies[0] . ' has recursive dependency');
        }

        return $this->initService($serviceName);
    }

    /**
     * @param $param
     * @return mixed
     */
    protected function getServiceParam($param)
    {
        if (strpos($param, '@') === 0) {
            $paramService = str_replace('@', '', $param);

            return $this->get($paramService, $asDependency = true);
        }
        elseif (strpos($param, ':') === 0) {
            $paramConfig = str_replace(':', '', $param);

            return $this->config->get($paramConfig);
        }

        return $param;
    }

    /**
     * @param $service
     * @return array
     */
    protected function getServiceInterfaces($service)
    {
        $aware = array();
        foreach ($this->containerConfig['container']['aware'] as $interface => $calls) {
            if ($service instanceof $interface) {
                foreach ($calls as $method => $param) {
                    $aware[$method] = $param;
                }
            }
        }

        return $aware;
    }
}