<?php
namespace Bun\Migration;

use Bun\Core\ApplicationInterface;
use Bun\Core\Config\ConfigAwareInterface;
use Bun\Core\Config\ConfigInterface;
use Bun\Core\Config\ApplicationConfig;
use Bun\Core\Container\Container;
use Bun\Core\Container\ContainerAwareInterface;
use Bun\Core\Container\ContainerInterface;
use Bun\Core\ObjectMapper\ObjectManagerAwareInterface;
use Bun\Core\ObjectMapper\ObjectMapperInterface;
use Bun\Core\ObjectMapper\ObjectMapperManager;
use Bun\Migration\Exception\MigrationException;
use Bun\PDO\PdoStorage;
use Bun\PDO\PdoStorageAwareInterface;
use Bun\Core\Repository\RepositoryManager;

/**
 * Class MigrationManager
 *
 * @package Bun\PDO\Migration
 */
class MigrationManager implements ConfigAwareInterface, ObjectManagerAwareInterface, PdoStorageAwareInterface, ContainerAwareInterface
{
    const MIGRATION_DONE = 1;
    const MIGRATION_FAILED = 0;
    const MIGRATION_IGNORED = 2;

    /** @var ObjectMapperManager */
    protected $objectManager;
    /** @var PdoStorage */
    protected $pdoStorage;
    /** @var ApplicationConfig */
    protected $config;
    /** @var string */
    protected $migrationModel;
    /** @var Container */
    protected $container;
    /** @var RepositoryManager  */
    protected $repositoryManager;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param ObjectMapperInterface $objectMapper
     */
    public function setObjectManager(ObjectMapperInterface $objectMapper)
    {
        $this->objectManager = $objectMapper;
    }

    /**
     * @param PdoStorage $pdoStorage
     */
    public function setPdoStorage(PdoStorage $pdoStorage)
    {
        $this->pdoStorage = $pdoStorage;
    }

    /**
     * @param ConfigInterface $config
     * @throws Exception\MigrationException
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        $this->migrationModel = $this->config->get('migration.model');
        if (!$this->migrationModel) {
            throw new MigrationException('Unable to proceed migration without migration.model config');
        }
        elseif (!class_exists($this->migrationModel)) {
            throw new MigrationException('Unable to proceed migration: migration.model class does not exists');
        }
        else {
            $test = new $this->migrationModel;
            if (!($test instanceof MigrationInterface)) {
                throw new MigrationException('migration.model should implements Bun\\Migration\\MigrationInterface');
            }
        }
    }

    /**
     * @return AbstractMigration[]
     */
    public function loadMigrations()
    {
        $applications = $this->config->getApplicationsList();
        $migrations = array();
        foreach ($applications as $appName => $app) {
            $loaded = $this->loadApplicationMigrations($app);
            foreach($loaded as $key => $val) {
                $migrations[$key] = $val;
            }
        }

        ksort($migrations);

        return $migrations;
    }

    /**
     * @param ApplicationInterface $app
     * @return AbstractMigration[]
     */
    public function loadApplicationMigrations($app)
    {
        $migrationDir = $app->getApplicationDir() . DIRECTORY_SEPARATOR . 'Migration';
        $migrations = array();
        if (is_dir($migrationDir)) {
            $dirHandler = opendir($migrationDir);
            $i = 0;
            while ($migrationFile = readdir($dirHandler)) {
                $i++;
                $fileName = $migrationDir . DIRECTORY_SEPARATOR . $migrationFile;
                if (is_file($fileName)) {
                    $migrationClass = str_replace('Application', '', get_class($app)) . 'Migration\\' . str_replace('.php', '', $migrationFile);
                    if (class_exists($migrationClass)) {
                        /** @var AbstractMigration $migration */
                        $migration = new $migrationClass;
                        if ($migration instanceof AbstractMigration) {
                            $migration->setObjectManager($this->objectManager);
                            $migration->setPdoStorage($this->pdoStorage);
                            $migration->setContainer($this->container);
                            $migrations[$migration->getName()] = $migration;
                        }
                    }
                }
            }
        }

        ksort($migrations);

        return $migrations;
    }

    /**
     * @param AbstractMigration $migration
     * @return bool
     */
    public function executeMigration(AbstractMigration $migration)
    {
        if ($this->notExecuted($migration)) {
            $done = $migration->execute();

            if ($done) {
                /** @var MigrationInterface $save */
                $save = new $this->migrationModel();
                $save
                    ->setName($migration->getName())
                    ->setDateTime(date('Y-m-d H:i:s'));

                $this->objectManager->save($save);

                return self::MIGRATION_DONE;
            }

            return self::MIGRATION_FAILED;
        }

        return self::MIGRATION_IGNORED;
    }

    /**
     * @return \Bun\Core\Repository\RepositoryInterface
     */
    public function getMigrationRepository()
    {
        return $this->getRepositoryManager()->getRepository($this->migrationModel);
    }

    /**
     * @return RepositoryManager|mixed
     */
    protected function getRepositoryManager()
    {
        if($this->repositoryManager === null) {
            $this->repositoryManager = $this->container->get('bun.core.repository_manager');
        }

        return $this->repositoryManager;
    }

    /**
     * @param AbstractMigration $migration
     * @return bool
     */
    public function notExecuted(AbstractMigration $migration)
    {
        $done = $this->getMigrationRepository()->findBy(array('name' => $migration->getName()));

        if (!$done) {
            return true;
        }

        return false;
    }
}