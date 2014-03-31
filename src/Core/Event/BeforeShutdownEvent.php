<?php
namespace Bun\Core\Event;

/**
 * Class BeforeShutdownEvent
 *
 * @package Bun\Core\Event
 */
class BeforeShutdownEvent extends AbstractEvent
{
    protected $name = 'bun.core.before_shutdown';


}