<?php
namespace Bun\Tool;

use Bun\Core\Http\Response;
use Bun\Tool\Controller\ToolController;

/**
 * Class ConsoleResponse
 *
 * @package Bun\Tool
 */
class ConsoleResponse extends Response
{
    const RESPONSE_OK = 0;
    const RESPONSE_FAIL = 1;
    const RESPONSE_WARNING = 2;
    const RESPONSE_CANCELED = 3;
    /** @var string */
    protected $responseColor;
    /** @var RunTimer */
    protected $timer;

    /**
     * @param string $message
     * @param int $resultCode
     * @param RunTimer $timer
     */
    public function __construct($message = '', $resultCode = self::RESPONSE_OK, $timer = null)
    {
        $this->headers = $resultCode;
        $this->content = $message;
        $this->timer = $timer;
    }

    /**
     * Defining output colorize
     */
    public function sendHeaders()
    {
        switch($this->headers) {
            case self::RESPONSE_OK:
                $this->responseColor = 'green';
                break;
            case self::RESPONSE_WARNING:
                $this->responseColor = 'yellow';
                break;
            case self::RESPONSE_FAIL:
                $this->responseColor = 'red';
                break;
            case self::RESPONSE_CANCELED:
                $this->responseColor = 'light_red';
                break;
        }
    }

    /**
     * @return string
     */
    public function getContent()
    {
        $runTime = ($this->timer instanceof RunTimer) ?
            'time: ' . $this->timer->getRunTime() . " s.\n":
            '';
        return ToolController::getColoredString($this->content, $this->responseColor) . "\n" . $runTime;
    }

    /**
     * @return int
     */
    public function getResultCode()
    {
        return $this->headers;
    }
}