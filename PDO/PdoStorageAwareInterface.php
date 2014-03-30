<?php
namespace Bun\PDO;

/**
 * Interface PdoStorageAwareInterface
 *
 * @package Bun\PDO
 */
interface PdoStorageAwareInterface
{
    /**
     * @param PdoStorage $pdoStorage
     * @return void
     */
    public function setPdoStorage(PdoStorage $pdoStorage);
}