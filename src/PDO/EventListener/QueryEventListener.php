<?php
namespace Bun\PDO\EventListener;

use Bun\Core\Event\EventDispatcherInterface;
use Bun\Logger\LoggerAwareInterface;
use Bun\Logger\LoggerInterface;
use Bun\PDO\Event\QueryEvent;

/**
 * Class QueryEventListener
 *
 * @package Bun\PDO\EventListener
 */
class QueryEventListener implements LoggerAwareInterface
{
    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param QueryEvent $event
     * @return int
     */
    public function onQuery(QueryEvent $event)
    {
        $query = $event->getQuery();
        $sql = $query->getQuery(false);
        $params = $query->getParameters();
        foreach ($params as $param) {
            $pos = strpos($sql, '?');
            if ($pos !== false) {
                $sql = substr_replace($sql, $param, $pos, 1);
            }
        }
        $query->getTime();
        $type = 'UNKNOWN';
        if ($query instanceof \SelectQuery) {
            $type = 'SELECT';
        }
        elseif ($query instanceof \InsertQuery) {
            $type = 'INSERT';
        }
        elseif ($query instanceof \UpdateQuery) {
            $type = 'UPDATE';
        }
        elseif ($query instanceof \DeleteQuery) {
            $type = 'DELETE';
        }

        $msg = $type . "\t" . $query->getFromTable() . "\t" . $sql ."\t time:" . round($query->getTime(), 4);
        $this->logger->log($msg, LoggerInterface::LOG_LEVEL_DEBUG, 'pdo_query');

        return EventDispatcherInterface::FLAG_PROPAGATION_CONTINUE;
    }
}