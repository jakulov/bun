<?php
namespace Bun\Core\Router;

/**
 * Class RoutingResult
 *
 * @package Bun\Core\Router
 */
class RoutingResult implements RoutingResultInterface
{
    protected $controllerClass;
    protected $actionName;
    protected $actionParams;

    /**
     * @return string
     */
    public function getControllerClass()
    {
        return $this->controllerClass;
    }

    /**
     * @param $class
     * @return $this
     */
    public function setControllerClass($class)
    {
        $this->controllerClass = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * @param $actionName
     * @return $this
     */
    public function setActionName($actionName)
    {
        $this->actionName = $actionName;

        return $this;
    }

    /**
     * @return array
     */
    public function getActionParams()
    {
        return $this->actionParams;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setActionParams($params = array())
    {
        $this->actionParams = $params;

        return $this;
    }

    /**
     * @return string
     */
    public function getActionNotFoundExceptionMessage()
    {
        return 'Action ' . $this->getActionName() . ' not found in controller: ' . $this->getControllerClass();
    }

    /**
     * @return string
     */
    public function getControllerNotFoundExceptionMessage()
    {
        return 'Controller not found: ' . $this->getControllerClass();
    }
}