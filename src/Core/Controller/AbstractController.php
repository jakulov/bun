<?php
namespace Bun\Core\Controller;

use Bun\Core\ApplicationInterface;
use Bun\Core\Container\ContainerInterface;
use Bun\Core\Exception\NotFoundException;
use Bun\Core\Exception\ResponseException;
use Bun\Core\Http\Response;
use Bun\Core\Http\ResponseInterface;
use Bun\Core\Config\ApplicationConfig;
use Bun\Core\Http\RequestInterface;
use Bun\Core\Router\RoutingResultInterface;
use Bun\Core\Repository\RepositoryManager;
use Bun\Tool\RunTimer;
use Bun\Form\FormBuilder;
use Bun\Core\ObjectMapper\ObjectMapperInterface;
use Bun\Session\SessionInterface;
use Bun\Assets\AssetManager;
use Bun\Logger\BunLogger;

/**
 * Class AbstractController
 *
 * @package Bun\Controller
 */
abstract class AbstractController implements ControllerInterface
{
    /** @var string */
    protected $defaultAction = 'index';
    /** @var ApplicationInterface */
    protected $application;
    /** @var RoutingResultInterface */
    protected $route;
    /** @var ContainerInterface */
    protected $container;
    /** @var RepositoryManager */
    protected $repositoryManager;
    /** @var RequestInterface */
    protected $request;
    /** @var ApplicationConfig */
    protected $config;
    /** @var RunTimer */
    protected $timer;
    /** @var FormBuilder */
    protected $formBuilder;
    /** @var ObjectMapperInterface */
    protected $objectManager;
    /** @var SessionInterface */
    protected $session;
    /** @var BunLogger */
    protected $logger;

    /** @var \Twig_Environment */
    protected $twig;
    /** @var \Smarty */
    protected $smarty2;

    protected $templateEngine = self::TEMPLATE_ENGINE_DEFAULT;
    /** @var AssetManager */
    protected $assetManager;

    protected $content = array();

    /**
     * @param RoutingResultInterface $route
     * @return ResponseInterface
     * @throws \Bun\Core\Exception\NotFoundException
     * @throws \Bun\Core\Exception\ResponseException
     */
    public function run(RoutingResultInterface $route)
    {
        $this->timer = new RunTimer(true);
        $this->route = $route;
        $this->init();

        $actionName =
            ($this->route->getActionName() ?
                $this->route->getActionName() :
                $this->defaultAction
            )
            . 'Action';
        if (method_exists($this, $actionName)) {
            /** @var ResponseInterface $response */
            $response = call_user_func_array(
                array($this, $actionName),
                $this->route->getActionParams()
            );
            if(!($response instanceof ResponseInterface)) {
                throw new ResponseException(
                    'Method '. get_class($this) .'::'. $actionName .
                    ' should return ResponseInterface instance'
                );
            }
        }
        else {
            throw new NotFoundException($this->route->getActionNotFoundExceptionMessage(), 404);
        }

        $this->shutdown();
        $response->setTimer($this->timer);

        return $response;
    }

    /**
     * @param $template
     * @param array $data
     * @param string $engine
     * @return string
     */
    public function render($template, $data = array(), $engine = null)
    {
        $data = array_replace_recursive($this->content, $data);

        if($engine === null) {
            $engine = $this->templateEngine;
        }
        if ($engine === self::TEMPLATE_ENGINE_TWIG) {
            if (strpos($template, '.html.twig') === false) {
                $template .= '.html.twig';
            }

            return $this->renderTwig($template, $data);
        }
        if ($engine === self::TEMPLATE_ENGINE_SMARTY2) {
            if (strpos($template, '.tpl') === false) {
                $template .= '.tpl';
            }

            return $this->renderSmarty2($template, $data);
        }

        if (strpos($template, '.phtml') === false) {
            $template .= '.phtml';
        }

        return $this->renderPhtml($template, $data);
    }

    /**
     * @param $template
     * @param array $data
     * @return string
     * @throws \Bun\Core\Exception\ResponseException
     */
    public function renderPhtml($template, $data = array())
    {
        extract($data);
        ob_start();

        $templateFile = $this->application->getApplicationDir() . DIRECTORY_SEPARATOR . self::VIEW_DIR .
            DIRECTORY_SEPARATOR . $template;
        if (file_exists($templateFile)) {
            require $templateFile;
        }
        else {
            throw new ResponseException('Unable to render view file: ' . $templateFile);
        }

        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * @param $template
     * @param array $data
     * @return string
     */
    public function renderTwig($template, $data = array())
    {
        if ($this->twig === null) {
            $this->initTwig();
        }

        return $this->twig->render($template, $data);
    }

    /**
     * Init Twig template engine
     */
    protected function initTwig()
    {
        $twigLoader = new \Twig_Loader_Filesystem(
            array(
                $this->application->getApplicationDir() . DIRECTORY_SEPARATOR . self::VIEW_DIR,
                __DIR__ . '/../' . self::VIEW_DIR
            )
        );

        $this->twig = new \Twig_Environment($twigLoader, array(
            'cache'       => VAR_DIR . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'Twig',
            'charset'     => PROJECT_ENCODING,
            'auto_reload' => true,
            'debug'       => ENV === ApplicationInterface::APPLICATION_ENV_DEV,
        ));

        $this->twig->addGlobal('ENV', ENV);
        $this->twig->addGlobal('PROJECT_ENCODING', PROJECT_ENCODING);
        $this->twig->addGlobal('request', $this->getRequest());
        $this->twig->addGlobal('config', $this->getConfig()->get());

        $assetHelper = $this->container->get('bun.assets.helper');
        $this->twig->addExtension($assetHelper);

        if(ENV === ApplicationInterface::APPLICATION_ENV_DEV) {
            $this->twig->addFunction('dump', new \Twig_SimpleFunction('dump', 'var_dump'));
        }
    }

    /**
     * @param $template
     * @param array $data
     * @return bool|mixed|string
     */
    public function renderSmarty2($template, $data = array())
    {
        if ($this->smarty2 === null) {
            $this->initSmarty2();
        }

        $this->smarty2->assign($data);

        return $this->smarty2->fetch($template);
    }

    /**
     * Init Smarty2 template engine
     */
    protected function initSmarty2()
    {
        $this->smarty2 = new \Smarty();
        if(!defined('SMARTY_DIR')) {
            define('SMARTY_DIR', LIB_DIR . DIRECTORY_SEPARATOR . 'Smarty');
        }
        $this->smarty2->template_dir = $this->application->getApplicationDir() . DIRECTORY_SEPARATOR . self::VIEW_DIR;
        $smartyCache = VAR_DIR . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'SmartyCache';
        if(!is_dir($smartyCache)) {
            mkdir($smartyCache, 0777, true);
        }
        $this->smarty2->cache_dir = $smartyCache;
        $smartyCompile = VAR_DIR . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'SmartyCompile';
        if(!is_dir($smartyCompile)) {
            mkdir($smartyCompile, 0777, true);
        }
        $this->smarty2->compile_dir = $smartyCompile;
        $this->smarty2->error_reporting = ENV === ApplicationInterface::APPLICATION_ENV_DEV ? E_ALL : null;
        $this->smarty2->debugging = ENV === ApplicationInterface::APPLICATION_ENV_DEV;

        $this->smarty2->assign('request', $this->getRequest());
        $this->smarty2->assign('ENV', ENV);
        $this->smarty2->assign('PROJECT_ENCODING', PROJECT_ENCODING);
    }

    protected function init()
    {

    }

    protected function shutdown()
    {

    }

    /**
     * @return Response
     */
    protected function indexAction()
    {
        $content = '<code><pre>' . print_r(func_get_args(), 1) . '</pre></code>';

        return new Response($content, array(), $this->timer);
    }

    /**
     * @param ApplicationInterface $app
     * @return $this
     */
    public function setApplication(ApplicationInterface $app)
    {
        $this->application = $app;

        return $this;
    }

    /**
     * @param ContainerInterface $container
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return RepositoryManager
     */
    public function getRepositoryManager()
    {
        if ($this->repositoryManager === null) {
            $this->repositoryManager = $this->container->get('bun.core.repository_manager');
        }

        return $this->repositoryManager;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        if ($this->request === null) {
            $this->request = $this->container->get('bun.core.http.request');
        }

        return $this->request;
    }

    /**
     * @return ApplicationConfig
     */
    public function getConfig()
    {
        if ($this->config === null) {
            $this->config = $this->container->get('bun.core.config');
        }

        return $this->config;
    }

    /**
     * @return FormBuilder
     */
    public function getFormBuilder()
    {
        if ($this->formBuilder === null) {
            /** @var formBuilder formBuilder */
            $this->formBuilder = $this->container->get('bun.form.builder');
        }

        return $this->formBuilder;
    }

    /**
     * @return ObjectMapperInterface
     */
    public function getObjectManager()
    {
        if ($this->objectManager === null) {
            $this->objectManager = $this->container->get('bun.core.object_manager');
        }

        return $this->objectManager;
    }

    /**
     * @param $url
     * @return Response
     */
    public function redirect($url)
    {
        $headers = array(
            'Location: ' . $url
        );

        return new Response('', $headers);
    }

    /**
     * @return SessionInterface
     */
    public function getSession()
    {
        if ($this->session === null) {
            $this->session = $this->container->get('bun.session');
        }

        return $this->session;
    }

    /**
     * @return AssetManager
     */
    public function getAssetManager()
    {
        if($this->assetManager === null) {
            $this->assetManager = $this->container->get('bun.assets.manager');
        }

        return $this->assetManager;
    }

    /**
     * @return BunLogger
     */
    public function getLogger()
    {
        if($this->logger === null) {
            $this->logger = $this->container->get('bun.logger');
        }

        return $this->logger;
    }
}