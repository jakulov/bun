<?php
namespace Bun\Core\Event;

/**
 * Class AfterShutdownEvent
 *
 * @package Bun\Core\Event
 */
class AfterShutdownEvent extends AbstractEvent
{
    protected $name = 'bun.core.after_shutdown';
}