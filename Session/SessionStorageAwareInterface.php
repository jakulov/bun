<?php
namespace Bun\Session;

/**
 * Interface SessionStorageAwareInterface
 *
 * @package Bun\Session
 */
interface SessionStorageAwareInterface
{
    /**
     * @param SessionStorageInterface $sessionStorage
     * @return void
     */
    public function setSessionStorage(SessionStorageInterface $sessionStorage);
}