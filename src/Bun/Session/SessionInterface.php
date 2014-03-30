<?php
namespace Bun\Session;

/**
 * Interface SessionInterface
 *
 * @package Bun\Session
 */
interface SessionInterface
{
    public function get($key);

    public function set($key, $value);

    public function getId();

    public function destroy();

    public function isStarted();

    public function start();

    public function setFlash($value);

    public function getFlash();

    public function save();

    public function sendCookie();
}