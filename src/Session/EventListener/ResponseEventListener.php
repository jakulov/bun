<?php
namespace Bun\Session\EventListener;

use Bun\Core\Event\BeforeResponseEvent;
use Bun\Core\Event\EventDispatcherInterface;
use Bun\Session\SessionAwareInterface;
use Bun\Session\SessionInterface;

/**
 * Class ResponseEventListener
 *
 * @package Bun\Session\EventListener
 */
class ResponseEventListener implements SessionAwareInterface
{
    /** @var SessionInterface */
    protected $session;

    /**
     * @param SessionInterface $session
     */
    public function setSession(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @param BeforeResponseEvent $event
     * @return int
     */
    public function onBeforeResponse(BeforeResponseEvent $event)
    {
        if(!$event->getRequest()->isConsole() && $this->session->isStarted()) {
            $this->session->save();
        }

        return EventDispatcherInterface::FLAG_PROPAGATION_CONTINUE;
    }
}