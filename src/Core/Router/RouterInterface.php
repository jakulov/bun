<?php
namespace Bun\Core\Router;

/**
 * Interface RouterInterface
 *
 * @package Bun\Core\Router
 */
interface RouterInterface
{
    const DEFAULT_ROUTING_ACTION = 'index';

    /**
     * @return RoutingResult
     */
    public function route();
}