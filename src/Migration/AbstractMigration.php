<?php
namespace Bun\Migration;

use Bun\Core\Container\Container;
use Bun\Core\Container\ContainerAwareInterface;
use Bun\Core\Container\ContainerInterface;
use Bun\Core\ObjectMapper\ObjectMapperInterface;
use Bun\PDO\PdoStorage;
use Bun\PDO\PdoStorageAwareInterface;

/**
 * Class AbstractMigration
 *
 * @package Bun\PDO\Migration
 */
abstract class AbstractMigration implements PdoStorageAwareInterface, ContainerAwareInterface
{
    /** @var ObjectMapperInterface */
    protected $objectManager;
    /** @var PdoStorage */
    protected $pdoStorage;
    /** @var Container */
    protected $container;

    /**
     * Should return unique migration name
     * @return string
     */
    abstract public function getName();

    /**
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->getPdoStorage()->getQueryBuilder()->getPdo();
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param ObjectMapperInterface $objectManager
     */
    public function setObjectManager(ObjectMapperInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param PdoStorage $pdoStorage
     */
    public function setPdoStorage(PdoStorage $pdoStorage)
    {
        $this->pdoStorage = $pdoStorage;
    }

    /**
     * @return PdoStorage
     */
    public function getPdoStorage()
    {
        return $this->pdoStorage;
    }

    /**
     * @return ObjectMapperInterface
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @return bool
     */
    abstract public function execute();

    /**
     * @return bool
     */
    abstract public function rollback();
}