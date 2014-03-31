<?php
namespace Bun\Core\Repository;

use Bun\Core\Config\ConfigAwareInterface;
use Bun\Core\Config\ConfigInterface;
use Bun\Core\ObjectMapper\ObjectManagerAwareInterface;
use Bun\Core\ObjectMapper\ObjectMapperInterface;
use Bun\Core\Model\ModelInterface;

/**
 * Class RepositoryManager
 *
 * @package Bun\Core\Repository
 */
class RepositoryManager implements ConfigAwareInterface, ObjectManagerAwareInterface
{
    /** @var ObjectMapperInterface */
    protected $objectMapper;

    /** @var ConfigInterface */
    protected $config;
    /** @var RepositoryInterface[] */
    protected $repositoryInstances = array();
    /** @var array  */
    protected $instancesCache = array();
    /** @var array */
    protected $repositories = array();

    /**
     * @param ObjectMapperInterface $objectMapper
     * @return mixed|void
     */
    public function setObjectManager(ObjectMapperInterface $objectMapper)
    {
        $this->objectMapper = $objectMapper;
    }

    /**
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        $this->repositories = $this->config->get('repository');
    }

    /**
     * @param $modelClassName
     * @return RepositoryInterface
     * @throws \Bun\Core\Repository\RepositoryException
     */
    public function getRepository($modelClassName)
    {
        if(!isset($this->repositoryInstances[$modelClassName])) {
            if(!isset($this->repositories[$modelClassName])) {
                /** @var ModelInterface $fooObject */
                $fooObject = new $modelClassName;
                $repositoryClass = $fooObject->getRepositoryName();
            }
            else {
                $repositoryClass = $this->repositories[$modelClassName];
            }

            if(class_exists($repositoryClass)) {
                if(!isset($this->instancesCache[$repositoryClass])) {
                    $repositoryInstance = new $repositoryClass;
                    if($repositoryInstance instanceof RepositoryInterface) {
                        $repositoryInstance->setModelClassName($modelClassName);
                        $repositoryInstance->setObjectManager($this->objectMapper);
                        $repositoryInstance->setConfig($this->config);

                        $this->repositoryInstances[$modelClassName] = $repositoryInstance;
                    }
                    else {
                        throw new RepositoryException(
                            'Repository class '. $repositoryClass .
                            ' should implements Bun\\Core\\Repository\\RepositoryInterface'
                        );
                    }
                }
                else {
                    $repositoryInstance = $this->instancesCache[$repositoryClass];
                    $this->repositoryInstances[$modelClassName] = $repositoryInstance;
                }
            }
            else {
                throw new RepositoryException('No repository class exists: '. $repositoryClass);
            }
        }

        return $this->repositoryInstances[$modelClassName];
    }
}