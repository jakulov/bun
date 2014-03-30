<?php
namespace Bun\Logger;

/**
 * Interface LoggerAwareInterface
 *
 * @package Bun\Logger
 */
interface LoggerAwareInterface
{
    /**
     * @param LoggerInterface $logger
     * @return void
     */
    public function setLogger(LoggerInterface $logger);
}