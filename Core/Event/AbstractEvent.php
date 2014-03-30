<?php
namespace Bun\Core\Event;

/**
 * Class AbstractEvent
 *
 * @package Bun\Core\Event
 */
abstract class AbstractEvent implements EventInterface
{
    /** @var string  */
    protected $name = 'bun.core.event';
    /** @var EventDispatcherAwareInterface */
    protected $sender;
    /** @var mixed */
    protected $data;

    /**
     * @param EventDispatcherAwareInterface $sender
     * @param $data
     */
    public function __construct(EventDispatcherAwareInterface $sender, $data)
    {
        $this->sender = $sender;
        $this->data = $data;

        $this->init();
    }

    /**
     * Call in Event constructor
     */
    protected function init()
    {
        // here implement your own event logic
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return EventDispatcherAwareInterface
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}