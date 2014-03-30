<?php
namespace Bun\Core\Event;

/**
 * Interface EventDispatcherInterface
 *
 * @package Bun\Core\Event
 */
interface EventDispatcherInterface
{
    const FLAG_PROPAGATION_CONTINUE = 0;
    const FLAG_PROPAGATION_STOP = 1;

    /**
     * @param EventInterface $event
     * @return int
     */
    public function dispatch(EventInterface $event);
}