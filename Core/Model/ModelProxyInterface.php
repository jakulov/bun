<?php
namespace Bun\Core\Model;

interface ModelProxyInterface
{
    /**
     * @return ModelInterface
     */
    public function getInstance();
}