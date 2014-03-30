<?php
namespace Bun\Core\Http;

use Bun\Tool\RunTimer;

/**
 * Interface ResponseInterface
 *
 * @package Bun\Core\Http
 */
interface ResponseInterface
{
    /**
     * @return array
     */
    public function getHeaders();

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders($headers = array());

    /**
     * @return void
     */
    public function sendHeaders();

    /**
     * @param $header
     * @return $this
     */
    public function setHeader($header);

    /**
     * @return mixed
     */
    public function getContent();

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content);

    /**
     * @return null|RunTimer
     */
    public function getTimer();

    /**
     * @param RunTimer $timer
     * @return void
     */
    public function setTimer(RunTimer $timer);
}