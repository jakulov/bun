<?php
namespace Bun\Core\Event;

/**
 * Interface EventInterface
 *
 * @package Bun\Core\Event
 */
interface EventInterface
{
    /**
     * @param EventDispatcherAwareInterface $sender
     * @param $data
     */
    public function __construct(EventDispatcherAwareInterface $sender, $data);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return EventDispatcherAwareInterface
     */
    public function getSender();

    /**
     * @return mixed
     */
    public function getData();
}