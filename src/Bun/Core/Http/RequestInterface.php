<?php
namespace Bun\Core\Http;

/**
 * Interface RequestInterface
 *
 * @package Bun\Core\Http
 */
interface RequestInterface
{
    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';

    public function uri();

    public function query($param = null);

    public function queryString();

    public function request($param = null);

    public function server($param = null);

    public function method();

    public function cookie($param = null);

    public function isConsole();

    public function isAjax();

    public function host();

    public function getConsoleArgs();

    public function ip();
}