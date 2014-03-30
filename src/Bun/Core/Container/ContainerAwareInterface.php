<?php
namespace Bun\Core\Container;

interface ContainerAwareInterface
{
    public function setContainer(ContainerInterface $container);
}