<?php
namespace Bun\Core\Config;

/**
 * Interface ConfigAwareInterface
 *
 * @package Bun\Core\Config
 */
interface ConfigAwareInterface
{
    public function setConfig(ConfigInterface $config);
}