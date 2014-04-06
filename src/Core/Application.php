<?php
namespace Bun\Core;

use Bun\Core\Config\ApplicationConfig;
use Bun\Core\Container\Container;
use Bun\Core\Controller\ErrorController;
use Bun\Core\Event\AfterResponseEvent;
use Bun\Core\Event\AfterShutdownEvent;
use Bun\Core\Event\BeforeResponseEvent;
use Bun\Core\Event\BeforeShutdownEvent;
use Bun\Core\Event\EventDispatcherAwareInterface;
use Bun\Core\Event\EventDispatcherInterface;
use Bun\Core\Event\RequestEvent;
use Bun\Core\Exception\Exception;
use Bun\Core\Exception\NotFoundException;
use Bun\Core\Exception\ResponseException;
use Bun\Core\Http\ResponseInterface;
use Bun\Core\Router\Router;
use Bun\Core\Controller\ControllerInterface;
use Bun\Core\Http\RequestInterface;
use Bun\Core\Router\RoutingException;
use Bun\Core\Router\RoutingResult;
use Bun\Core\Event\EventDispatcher;

/**
 * Class Application
 *
 * @package Bun\Core
 */
class Application implements ApplicationInterface, EventDispatcherAwareInterface
{
    /** @var string */
    protected $env;
    /** @var string */
    protected $name = 'Core';
    /** @var string */
    protected $appDir = __DIR__;
    /** @var Config\ApplicationConfig */
    protected $config;
    /** @var Container */
    protected $container;
    /** @var EventDispatcher */
    protected $eventDispatcher;

    /**
     * @param string $env
     */
    public function __construct($env = self::APPLICATION_ENV_DEV)
    {
        $this->env = $env;
        /**
         * You need to define this constants in your application index file
         */
        if(!defined('ENV')) define('ENV', 'dev'); // environment
        $root = __DIR__ .'/../../../../..';
        if(!defined('APP_DIR')) define('APP_DIR', $root .'/app'); // web root dir
        if(!defined('SRC_DIR')) define('SRC_DIR', $root .'/src'); // sources of application
        if(!defined('LIB_DIR')) define('LIB_DIR', $root .'/vendor'); // vendors dir
        if(!defined('BUN_DIR')) define('BUN_DIR', LIB_DIR . '/bun/bun/src'); // bun framework dir
        if(!defined('VAR_DIR')) define('VAR_DIR', $root .'/var'); // directory for files
        if(!defined('TMP_DIR')) define('TMP_DIR', '/tmp'); // tmp directory
        if(!defined('PUBLIC_DIR')) define('PUBLIC_DIR', APP_DIR .'/public'); // directory for static files
        if(!defined('PUBLIC_PATH')) define('PUBLIC_PATH', '/public'); // web path to static files
        if(!defined('SECRET_SALT')) define('SECRET_SALT', 'bun'); // secret sal for hashing
        if(!defined('PROJECT_ENCODING')) define('PROJECT_ENCODING', 'utf-8'); // encoding of your application
        if(!defined('UNICODE')) define('UNICODE', 'utf-8'); // unicode encoding, no need to redefine :-)

        if (!ini_get('date.timezone')) {
            date_default_timezone_set('UTC'); // your application timezone
        }
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->env;
    }

    /**
     * @return string
     */
    public function getApplicationName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getApplicationDir()
    {
        return $this->appDir;
    }

    /**
     * @throws NotFoundException
     */
    public function run()
    {
        /** @var ApplicationConfig $config */
        $this->config = new ApplicationConfig($this);
        /** @var Container $container */
        $this->container = Container::getInstance($this->config);

        if (!defined('BUN_SILENT_MODE')) {
            /** @var RequestInterface $request */
            $request = $this->container->get('bun.core.http.request');
            /** @var EventDispatcher $eventDispatcher */
            $eventDispatcher = $this->container->get('bun.core.event_dispatcher');
            $this->setEventDispatcher($eventDispatcher);

            if (!$request->isConsole()) {
                ob_start();
            }

            $this->dispatchRunEvents($request);

            try {
                $response = $this->runController();
            }
            catch (Exception $e) {
                $response = $this->handleError($e);
            }

            $this->dispatchBeforeResponseEvent($response, $request);
            $response->sendHeaders();
            echo $response->getContent();
            $this->dispatchAfterResponseEvent($response);

            if (!$request->isConsole()) {
                ob_end_flush();
            }

            $this->shutdown();
        }
    }

    /**
     * @param ResponseInterface $response
     * @param RequestInterface $request
     */
    protected function dispatchBeforeResponseEvent(ResponseInterface $response, RequestInterface $request)
    {
        $event = new BeforeResponseEvent($this, array('response' => $response, 'request' => $request));
        $this->eventDispatcher->dispatch($event);
    }

    /**
     * @param ResponseInterface $response
     */
    protected function dispatchAfterResponseEvent(ResponseInterface $response)
    {
        $event = new AfterResponseEvent($this, array('response' => $response));
        $this->eventDispatcher->dispatch($event);
    }

    /**
     * @param RequestInterface $request
     * @return int
     */
    protected function dispatchRunEvents(RequestInterface $request)
    {
        $event = new RequestEvent($this, array('request' => $request));

        return $this->eventDispatcher->dispatch($event);
    }

    /**
     * @param Exception $exception
     * @return ResponseInterface
     */
    protected function handleError(Exception $exception)
    {
        $route = new RoutingResult();
        $route
            ->setControllerClass('Bun\\Core\\Controller\\ErrorController')
            ->setActionName('index')
            ->setActionParams(
                array('exception' => $exception)
            );

        $controller = new ErrorController();

        return $controller
            ->setApplication($this)
            ->setContainer($this->container)
            ->run($route);
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @return void
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws ResponseException
     */
    protected function runController()
    {
        /** @var Router $router */
        $router = $this->container->get('bun.core.router');
        try {
            $route = $router->route();
        }
        catch (RoutingException $e) {
            return $this->handleError($e);
        }

        $controllerClass = $route->getControllerClass();
        if (class_exists($controllerClass)) {
            /** @var $controller ControllerInterface */
            $controller = new $controllerClass();
            $response = $controller
                ->setApplication($this)
                ->setContainer($this->container)
                ->run($route);

            if (!($response instanceof ResponseInterface)) {
                throw new ResponseException(
                    $route->getControllerClass() . '::' . $route->getActionName() .
                    'Action returned not a valid Response',
                    500
                );
            }
        }
        else {
            throw new NotFoundException($route->getControllerNotFoundExceptionMessage(), 404);
        }

        return $response;
    }

    /**
     * Shutdown application
     */
    protected function shutdown()
    {
        $this->dispatchBeforeShutdownEvent();
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        $this->dispatchAfterShutdownEvent();
    }

    /**
     *
     */
    protected function dispatchBeforeShutdownEvent()
    {
        $event = new BeforeShutdownEvent($this, array());
        $this->eventDispatcher->dispatch($event);
    }

    /**
     *
     */
    protected function dispatchAfterShutdownEvent()
    {
        $event = new AfterShutdownEvent($this, array());
        $this->eventDispatcher->dispatch($event);
    }
}