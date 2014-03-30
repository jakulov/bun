<?php
namespace Bun\Core\Controller;

use Bun\Core\ApplicationInterface;
use Bun\Core\Container\ContainerInterface;
use Bun\Core\Router\RoutingResultInterface;
use Bun\Core\Http\ResponseInterface;

/**
 * Interface ControllerInterface
 *
 * @package Bun\Controller
 */
interface ControllerInterface
{
    const TEMPLATE_ENGINE_DEFAULT = 'phtml';
    const TEMPLATE_ENGINE_TWIG = 'Twig';
    const TEMPLATE_ENGINE_SMARTY2 = 'Smarty2';

    const VIEW_DIR = 'View';

    /**
     * @param ApplicationInterface $app
     * @return $this
     */
    public function setApplication(ApplicationInterface $app);

    /**
     * @param RoutingResultInterface $route
     * @return ResponseInterface
     */
    public function run(RoutingResultInterface $route);

    /**
     * @param ContainerInterface $container
     * @return $this
     */
    public function setContainer(ContainerInterface $container);

    /**
     * @return ContainerInterface
     */
    public function getContainer();
}