<?php
namespace Bun\Core\Http;

/**
 * Interface RequestAwareInterface
 *
 * @package Bun\Core\Http
 */
interface RequestAwareInterface
{
    public function setRequest(RequestInterface $request);
}