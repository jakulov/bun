<?php
namespace Bun\Core\Event;

use Bun\Core\Config\ConfigAwareInterface;
use Bun\Core\Config\ConfigInterface;
use Bun\Core\Container\ContainerAwareInterface;
use Bun\Core\Container\ContainerException;
use Bun\Core\Container\ContainerInterface;

/**
 * Class EventDispatcher
 *
 * @package Bun\Core\Event
 */
class EventDispatcher implements EventDispatcherInterface, ConfigAwareInterface, ContainerAwareInterface
{
    /** @var ConfigInterface */
    protected $config;
    /** @var ContainerInterface */
    protected $container;
    /** @var array */
    protected $listeners = array();

    /**
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        $this->initListeners();
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Initialize listeners config
     */
    protected function initListeners()
    {
        $this->listeners = $this->config->get('event');
    }

    /**
     * @param EventInterface $event
     * @return int
     * @throws EventDispatcherException
     */
    public function dispatch(EventInterface $event)
    {
        $propagationFlag = self::FLAG_PROPAGATION_CONTINUE;
        $eventName = $event->getName();
        if (isset($this->listeners[$eventName])) {
            foreach ($this->listeners[$eventName] as $listenerService => $listenerMethod) {
                try {
                    $listener = $this->container->get( str_replace('@', '', $listenerService) );
                    if(method_exists($listener, $listenerMethod)) {
                        $propagationFlag = call_user_func_array(array($listener, $listenerMethod), array($event));
                        if($propagationFlag === self::FLAG_PROPAGATION_STOP) {
                            return $propagationFlag;
                        }
                    }
                    else {
                        throw new EventDispatcherException(
                            'Listener method '. $listenerMethod .' not exists in class '. get_class($listener)
                        );
                    }
                }
                catch (ContainerException $e) {
                    throw new EventDispatcherException(
                        'Unable to get event '. $eventName .' listener service: '. $listenerService,
                        0,
                        $e
                    );
                }
            }
        }

        return $propagationFlag;
    }
}