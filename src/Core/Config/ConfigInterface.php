<?php
namespace Bun\Core\Config;

/**
 * Interface ConfigInterface
 *
 * @package Bun\Core\Config
 */
interface ConfigInterface
{
    const BUN_NAMESPACE = 'Bun';
    const CONFIG_NAMESPACE = 'Config';

    public function getConfig($env);

    public function getName();

    public function get($param = null);

    public function getEnvironment();
}