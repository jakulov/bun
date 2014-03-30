<?php
namespace Bun\Core\Router;

use Bun\Core\Config\ConfigAwareInterface;
use Bun\Core\Config\ConfigInterface;
use Bun\Core\Exception\NotFoundException;
use Bun\Core\Http\RequestAwareInterface;
use Bun\Core\Http\RequestInterface;

/**
 * Class Router
 *
 * @package Bun\Core\Router
 */
class Router implements RouterInterface, ConfigAwareInterface, RequestAwareInterface
{
    /** @var ConfigInterface */
    protected $config;
    /** @var RequestInterface */
    protected $request;
    /** @var array */
    protected $routes = array();

    protected $methods = array(
        'GET',
        'POST',
        'PUT',
        'DELETE'
    );

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param RequestInterface $request
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @return RoutingResult
     * @throws RoutingException
     */
    public function route()
    {
        if ($this->request->isConsole()) {
            return $this->routeTool();
        }

        $routes = $this->config->get('router');
        $uri = explode('?', $this->request->uri());
        $uri = $uri[0];
        $routesUris = array_keys($routes);

        $routingResult = new RoutingResult();
        $matchedRoute = in_array($uri, $routesUris) ?
            $matchedRoute = $routes[$uri] :
            $matchedRoute = $this->match($routes);

        if ($matchedRoute) {
            $routingResult
                ->setControllerClass($matchedRoute['controller'])
                ->setActionName(isset($matchedRoute['action']) ?
                        $matchedRoute['action'] :
                        self::DEFAULT_ROUTING_ACTION
                )
                ->setActionParams(isset($matchedRoute['params']) ?
                        $matchedRoute['params'] :
                        array()
                );

            return $routingResult;
        }

        throw new RoutingException(
            'Unable to match route for: ' . $this->request->method() . ': ' . $uri
        );
    }

    /**
     * @return RoutingResult
     * @throws RoutingException
     */
    protected function routeTool()
    {
        $toolsConfig = $this->config->get('tool');
        $requestArgs = $this->request->getConsoleArgs();
        $requestTool = isset($requestArgs[1]) ? $requestArgs[1] : 'bun.tool';
        $toolParts = explode(':', $requestTool);
        $toolName = $toolParts[0];
        $toolAction = isset($toolParts[1]) ? $toolParts[1] : 'index';
        $toolParams = array_slice($toolParts, 2);

        if (is_array($toolsConfig) && isset($toolsConfig[$toolName])) {
            $toolControllerClass = $toolsConfig[$toolName];
            if (class_exists($toolControllerClass)) {
                $routingResult = new RoutingResult();
                $routingResult
                    ->setControllerClass($toolControllerClass)
                    ->setActionName($toolAction)
                    ->setActionParams($toolParams);

                return $routingResult;
            }
        }

        throw new RoutingException('Requested tool: ' . $toolName . ' could not be found');
    }

    /**
     * @param array $routes
     * @return bool|array
     */
    protected function match($routes)
    {
        $uri = explode('?', $this->request->uri());
        $uri = $uri[0];
        $method = $this->request->method();
        $routesUri = array_keys($routes);
        $matchedRoute = false;
        foreach ($routesUri as $routeUri) {
            if (strpos($routeUri, ':') !== false) {
                $routeMethods = isset($routes[$routeUri]['method']) ?
                    $routes[$routeUri]['method'] :
                    $this->methods;
                $regexRouteUri = str_replace('/', '\\/', $routeUri);
                $regexRouteUri = '/' . preg_replace('/:(\w+)/', '\w+', $regexRouteUri) . '$/';
                if (preg_match($regexRouteUri, $uri, $matches) && in_array($method, $routeMethods)) {
                    $matchedRoute = $routes[$routeUri];
                    preg_match_all('/:\w+/', $routeUri, $routeParams);
                    preg_match_all('/\w+/', $routeUri, $routeParts);
                    preg_match_all('/\w+/', $uri, $uriParts);
                    $actionParams = array();
                    foreach ($routeParts[0] as $key => $paramName) {
                        if (in_array(':' . $paramName, $routeParams[0])) {
                            $actionParams[$paramName] = isset($uriParts[0][$key]) ?
                                $uriParts[0][$key] :
                                null;
                        }
                    }
                    $matchedRoute['params'] = $actionParams;

                    break;
                }
            }
        }

        return $matchedRoute;
    }
}