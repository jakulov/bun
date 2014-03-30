<?php
namespace Bun\PDO\Event;

use Bun\Core\Event\AbstractEvent;

/**
 * Class QueryEvent
 *
 * @package Bun\PDO\Event
 */
class QueryEvent extends AbstractEvent
{
    protected $name = 'bun.pdo.query';
    /** @var \BaseQuery */
    protected $query;

    /**
     * Init query data
     */
    protected function init()
    {
        $this->query = $this->data['query'];
    }

    /**
     * @return \BaseQuery
     */
    public function getQuery()
    {
        return $this->query;
    }
}