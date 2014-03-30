<?php
namespace Bun\Core\Container;

use Bun\Core\Config\ConfigInterface;

/**
 * Interface ContainerInterface
 *
 * @package Bun\Core\Container
 */
interface ContainerInterface
{
    public static function getInstance(ConfigInterface $config);

    public function get($serviceName);
}