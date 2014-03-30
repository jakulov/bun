<?php
namespace Bun\Core\Repository;

/**
 * Interface RepositoryManagerAwareInterface
 *
 * @package Bun\Core\Repository
 */
interface RepositoryManagerAwareInterface
{
    /**
     * @param RepositoryManager $repositoryManager
     */
    public function setRepositoryManager(RepositoryManager $repositoryManager);
}