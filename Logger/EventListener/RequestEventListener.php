<?php
namespace Bun\Logger\EventListener;

use Bun\Core\Event\EventDispatcherInterface;
use Bun\Core\Event\RequestEvent;
use Bun\Logger\LoggerAwareInterface;
use Bun\Logger\LoggerInterface;

/**
 * Class RequestEventListener
 *
 * @package Bun\Logger\EventListener
 */
class RequestEventListener implements LoggerAwareInterface
{
    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param RequestEvent $event
     * @return int
     */
    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $this->logger->log('Request '. $request->method() .' '. $request->uri(), LoggerInterface::LOG_LEVEL_DEBUG);

        return EventDispatcherInterface::FLAG_PROPAGATION_CONTINUE;
    }
}