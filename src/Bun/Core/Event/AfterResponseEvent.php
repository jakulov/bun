<?php
namespace Bun\Core\Event;

use Bun\Core\Http\ResponseInterface;

/**
 * Class AfterResponseEvent
 *
 * @package Bun\Core\Event
 */
class AfterResponseEvent extends AbstractEvent
{
    protected $name = 'bun.core.after_response';

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->data['response'];
    }
}