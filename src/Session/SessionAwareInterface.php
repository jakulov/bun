<?php
namespace Bun\Session;

/**
 * Interface SessionAwareInterface
 *
 * @package Bun\Session
 */
interface SessionAwareInterface
{
    /**
     * @param SessionInterface $session
     * @return void
     */
    public function setSession(SessionInterface $session);
}