<?php
namespace Bun\Core\ObjectMapper;

/**
 * Interface ObjectMapperAwareInterface
 *
 * @package Bun\Core\ObjectMapper
 */
interface ObjectManagerAwareInterface
{
    /**
     * @param ObjectMapperInterface $objectMapper
     */
    public function setObjectManager(ObjectMapperInterface $objectMapper);
}