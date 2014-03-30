<?php
namespace Bun\Session;

/**
 * Interface SessionStorageInterface
 *
 * @package Bun\Session
 */
interface SessionStorageInterface
{
    /**
     * @param $name
     * @return mixed
     */
    public function get($name);

    /**
     * @param $name
     * @param $value
     * @param $ttl
     * @return bool
     */
    public function set($name, $value, $ttl = null);

    /**
     * @param $name
     * @return bool
     */
    public function delete($name);

    /**
     * @return string
     */
    public function generateId();
}