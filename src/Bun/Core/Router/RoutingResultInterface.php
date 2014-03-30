<?php
namespace Bun\Core\Router;

/**
 * Interface RoutingResultInterface
 *
 * @package Bun\Core\Router
 */
interface RoutingResultInterface
{
    const DEFAULT_ACTION_NAME = 'index';

    /**
     * @return mixed
     */
    public function getControllerClass();

    /**
     * @return mixed
     */
    public function getActionName();

    /**
     * @return mixed
     */
    public function getActionParams();

    /**
     * @return mixed
     */
    public function getActionNotFoundExceptionMessage();

    /**
     * @return mixed
     */
    public function getControllerNotFoundExceptionMessage();
}