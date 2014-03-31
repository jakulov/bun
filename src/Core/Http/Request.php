<?php
namespace Bun\Core\Http;

/**
 * Class Request
 *
 * @package Bun\Core\Http
 */
class Request implements RequestInterface
{
    protected $uri;
    protected $host;
    protected $request;
    protected $query;
    protected $cookie;
    protected $server;
    protected $method;
    protected $files;
    protected $isAjaxHttpRequest;
    protected $isConsoleRequest;
    protected $consoleArgs;
    protected $ip;

    /**
     * Initialize request params
     */
    public function __construct()
    {
        global $argv;
        $this->request = $_POST;
        $this->query = $_GET;
        $this->server = $_SERVER;
        $this->cookie = $_COOKIE;
        $this->uri = isset($this->server['REQUEST_URI']) ? $this->server['REQUEST_URI'] : '';
        $this->host = isset($this->server['HTTP_HOST']) ? $this->server['HTTP_HOST'] : '';
        $this->method = isset($this->server['REQUEST_METHOD']) ? strtoupper($this->server['REQUEST_METHOD']) : '';
        $this->ip = isset($this->server['REMOTE_ADDR']) ? $this->server['REMOTE_ADDR'] : '';
        $this->files = $_FILES;
        $this->isConsoleRequest = defined('PHP_SAPI') && PHP_SAPI === 'cli';
        $this->isAjaxHttpRequest =
            isset($this->server['HTTP_X_REQUESTED_WITH']) &&
            strtolower($this->server['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if($this->isConsoleRequest) {
            $this->consoleArgs = $argv;
        }
    }

    /**
     * @return string
     */
    public function ip()
    {
        return $this->ip;
    }

    /**
     * @param $param
     * @return mixed|null
     */
    public function query($param = null)
    {
        if ($param === null) {
            return $this->query;
        }
        elseif (isset($this->query[$param])) {
            return $this->query[$param];
        }

        return null;
    }

    /**
     * @param $param
     * @return null|mixed
     */
    public function cookie($param = null)
    {
        if($param === null) {
            return $this->cookie;
        }
        elseif(isset($this->cookie[$param])) {
            return $this->cookie[$param];
        }

        return null;
    }

    /**
     * @return string
     */
    public function queryString()
    {
        return http_build_query($this->query);
    }

    /**
     * @return string
     */
    public function uri()
    {
        return $this->uri;
    }

    /**
     * @return string
     */
    public function host()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * @param null $param
     * @return null
     */
    public function request($param = null)
    {
        if ($param === null) {
            return $this->request;
        }
        elseif (isset($this->request[$param])) {
            return $this->request[$param];
        }

        return null;
    }

    /**
     * @param null $param
     * @return null
     */
    public function server($param = null)
    {
        if ($param === null) {
            return $this->server;
        }
        elseif (isset($this->server[$param])) {
            return $this->server[$param];
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isAjax()
    {
        return $this->isAjaxHttpRequest;
    }

    /**
     * @return bool
     */
    public function isConsole()
    {
        return $this->isConsoleRequest;
    }

    /**
     * @return array
     */
    public function getConsoleArgs()
    {
        return $this->consoleArgs;
    }
}