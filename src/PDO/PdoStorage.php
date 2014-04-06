<?php
namespace Bun\PDO;

use Bun\Core\ApplicationInterface;
use Bun\Core\Cache\CacheDriverAwareInterface;
use Bun\Core\Cache\CacheDriverInterface;
use Bun\Core\Config\ConfigAwareInterface;
use Bun\Core\Config\ConfigInterface;
use Bun\Core\Event\EventDispatcherAwareInterface;
use Bun\Core\Event\EventDispatcherInterface;
use Bun\Core\Storage\StorageInterface;
use Bun\PDO\Event\QueryEvent;

require_once BUN_DIR . '/../lib/FluentPDO/FluentPDO.php';
/**
 * Class PdoStorage
 *
 * @package MySQL
 */
class PdoStorage implements StorageInterface, ConfigAwareInterface, EventDispatcherAwareInterface, CacheDriverAwareInterface
{
    /** @var string */
    protected $table;
    /** @var ConfigInterface */
    protected $config;
    /** @var \FluentPDO */
    protected $queryBuilder;
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    /** @var CacheDriverInterface */
    protected $cacheDriver;

    /**
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param CacheDriverInterface $cacheDriver
     * @return void
     */
    public function setCacheDriver(CacheDriverInterface $cacheDriver)
    {
        $this->cacheDriver = $cacheDriver;
    }

    /**
     * @return CacheDriverInterface
     */
    public function getCacheDriver()
    {
        return $this->cacheDriver;
    }

    /**
     * Create new PDO connection
     */
    protected function init()
    {
        $pdoConfig = $this->config->get('pdo.pdo');
        try {
            $pdo = new \PDO(
                $pdoConfig['dns'],
                $pdoConfig['username'],
                $pdoConfig['password'],
                $pdoConfig['options']
            );
            if (ENV === ApplicationInterface::APPLICATION_ENV_DEV) {
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            }
            else {
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
            }

            $pdo->query('set character set cp1251');
            $pdo->query('set character_set_client=\'cp1251\'');
            $pdo->query('set names cp1251');
        }
        catch (\PDOException $e) {
            throw new PdoStorageException('Unable to connect to pdo database', 0, $e);
        }

        $queryBuilder = new \FluentPDO($pdo);
        if (ENV === ApplicationInterface::APPLICATION_ENV_DEV) {
            $queryBuilder->debug = array($this, 'dispatchQueryEvent');
        }
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @param \BaseQuery $query
     */
    public function dispatchQueryEvent(\BaseQuery $query)
    {
        $event = new QueryEvent($this, array('query' => $query));
        $this->eventDispatcher->dispatch($event);
    }

    /**
     * @return \FluentPDO
     */
    public function getQueryBuilder()
    {
        if ($this->queryBuilder === null) {
            $this->init();
        }

        return $this->queryBuilder;
    }

    /**
     * @param $table
     * @return $this
     */
    public function table($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @param $id
     * @return array|mixed|null
     */
    public function find($id)
    {
        $query = $this->getQueryBuilder()
            ->from($this->table)
            ->where('id = ?', $id);

        $result = $query->fetchAll();

        return isset($result[0]) ? $result[0] : null;
    }

    /**
     * @param array $where
     * @param array $orderBy
     * @param array $limit
     * @param bool $useCache
     * @return array|mixed
     */
    public function findBy($where, $orderBy = array(), $limit = array(), $useCache = false)
    {
        $query = $this->getQueryBuilder()->from($this->table);

        foreach ($where as $field => $value) {
            if (strpos($field, ' ') === false && !is_array($value)) {
                $field .= ' = ?';
            }
            $query->where($field, $value);
        }

        if ($orderBy) {
            $orderByStr = array();
            foreach ($orderBy as $field => $sort) {
                $orderByStr[] = $field . ' ' . $sort;
            }
            $query->orderBy(join(', ', $orderByStr));
        }

        if ($limit) {
            $query
                ->offset($limit[0])
                ->limit($limit[1]);
        }

        return $query->fetchAll();
    }

    /**
     * @param $data
     * @return bool|int
     */
    public function insert($data)
    {
        $query = $this->getQueryBuilder()->insertInto($this->table, $data);
        $id = $query->execute();
        if ($id) {
            return $id;
        }

        return false;
    }

    /**
     * @param $where
     * @param $data
     * @return bool|int
     */
    public function update($data, $where)
    {
        $query = $this->getQueryBuilder()->update($this->table, $data);
        foreach ($where as $field => $value) {
            if (strpos($field, ' ') === false && !is_array($value)) {
                $field .= ' = ?';
            }
            $query->where($field, $value);
        }

        return $query->execute();
    }

    /**
     * @param $where
     * @return bool|int
     * @throws PdoStorageException
     */
    public function delete($where)
    {
        if(isset($where['id'])) {
            try {
                $query = $this->getQueryBuilder()->deleteFrom($this->table, $where['id']);

                return $query->execute();
            }
            catch(\PDOException $e) {
                throw new PdoStorageException('Unable to delete record', 0, $e);
            }
        }

        return false;
    }
}