<?php
namespace Bun\Core\Http;

use Bun\Tool\RunTimer;

/**
 * Class Response
 *
 * @package Bun\Core\Http
 */
class Response implements ResponseInterface
{
    /**
     * @var array
     */
    protected $headers = array();
    /**
     * @var string
     */
    protected $content = '';
    /** @var RunTimer */
    protected $timer;

    /**
     * @param null $content
     * @param array $headers
     * @param RunTimer $timer
     */
    public function __construct($content = null, $headers = array(), $timer = null)
    {
        if ($content !== null) {
            $this->setContent($content);
        }

        if (count($headers) > 0) {
            $this->setHeaders($headers);
        }

        if($timer !== null) {
            $this->setTimer($timer);
        }
    }

    public function setTimer(RunTimer $timer)
    {
        $this->timer = $timer;
    }

    /**
     * @return RunTimer|null
     */
    public function getTimer()
    {
        return $this->timer;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param $header
     * @param int $code
     * @return $this
     */
    public function setHeader($header, $code = 200)
    {
        $this->headers[(string)$code] = $header;

        return $this;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders($headers = array())
    {
        foreach ($headers as $code => $header) {
            $this->setHeader($header, $code);
        }

        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return void
     */
    public function sendHeaders()
    {
        // TODO: response codes of header
        foreach ($this->headers as $code => $header) {
            header($header, true, (int)$code);
        }
    }
}