<?php
namespace Bun\Logger\EventListener;

use Bun\Core\Event\AfterResponseEvent;
use Bun\Core\Event\EventDispatcherInterface;
use Bun\Core\Http\RequestAwareInterface;
use Bun\Core\Http\RequestInterface;
use Bun\Logger\LoggerAwareInterface;
use Bun\Logger\LoggerInterface;
use Bun\Tool\RunTimer;

/**
 * Class ResponseEventListener
 *
 * @package Bun\Logger\EventListener
 */
class ResponseEventListener implements LoggerAwareInterface, RequestAwareInterface
{
    /** @var LoggerInterface */
    protected $logger;
    /** @var RequestInterface */
    protected $request;

    /**
     * @param RequestInterface $request
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param AfterResponseEvent $event
     * @return int
     */
    public function onAfterResponse(AfterResponseEvent $event)
    {
        $timer = $event->getResponse()->getTimer();
        if ($timer instanceof RunTimer && $timer->isStarted()) {
            $time = $timer->getRunTime();
            $logLevel = LoggerInterface::LOG_LEVEL_DEBUG;
            if ($time >= 1) {
                $logLevel = LoggerInterface::LOG_LEVEL_WARNING;
            }

            if ($this->request->isConsole()) {
                $requestLog = join(' ', $this->request->getConsoleArgs());
            }
            else {
                $requestLog = $this->request->method() . ' ' . $this->request->host() . $this->request->uri();
                if ($this->request->query()) {
                    $requestLog .= '?' . $this->request->queryString();
                }
            }

            $this->logger->log(
                'Request ' . $requestLog .
                ' Response time: ' . $time,
                $logLevel,
                'runtime'
            );
        }

        return EventDispatcherInterface::FLAG_PROPAGATION_CONTINUE;
    }
}