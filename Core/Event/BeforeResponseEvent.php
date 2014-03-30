<?php
namespace Bun\Core\Event;

use Bun\Core\Http\ResponseInterface;
use Bun\Core\Http\RequestInterface;

/**
 * Class BeforeResponseEvent
 *
 * @package Bun\Core\Event
 */
class BeforeResponseEvent extends AbstractEvent
{
    protected $name = 'bun.core.before_response';

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->data['response'];
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->data['request'];
    }
}