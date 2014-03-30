<?php
namespace Bun\Core\Event;

/**
 * Interface EventDispatcherAwareInterface
 *
 * @package Bun\Core\Event
 */
interface EventDispatcherAwareInterface
{
    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @return void
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher);
}