<?php
namespace Bun\Core\Event;

use Bun\Core\Http\RequestInterface;

/**
 * Class RequestEvent
 *
 * @package Bun\Core\Event
 */
class RequestEvent extends AbstractEvent
{
    protected $name = 'bun.core.request';

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->data['request'];
    }
}