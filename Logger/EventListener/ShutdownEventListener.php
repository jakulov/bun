<?php
namespace Bun\Logger\EventListener;

use Bun\Core\Event\AfterShutdownEvent;
use Bun\Core\Event\EventDispatcherInterface;
use Bun\Logger\LoggerAwareInterface;
use Bun\Logger\LoggerInterface;

/**
 * Class ShutdownEventListener
 *
 * @package Bun\Logger\EventListener
 */
class ShutdownEventListener implements LoggerAwareInterface
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
     * @param AfterShutdownEvent $event
     * @return int
     */
    public function onAfterShutdown(AfterShutdownEvent $event)
    {
        $this->logger->log('After shutdown event captured', LoggerInterface::LOG_LEVEL_DEBUG);
        $this->logger->flushLogs();
        return EventDispatcherInterface::FLAG_PROPAGATION_CONTINUE;
    }
}