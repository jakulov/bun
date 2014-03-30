<?php
namespace Bun\Core\Http;

/**
 * Class AjaxResponse
 *
 * @package Bun\Core\Http
 */
class AjaxResponse extends Response
{
    /**
     * @return mixed|string
     */
    public function getContent()
    {
        return json_encode($this->content);
    }

    /**
     *
     */
    public function sendHeaders()
    {
        if(!$this->headers) {
            header('Content-type: application/json', true, 200);
        }
        else {
            parent::sendHeaders();
        }
    }
}