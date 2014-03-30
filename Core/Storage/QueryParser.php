<?php
namespace Bun\Core\Storage;

/**
 * Class QueryParser
 *
 * @package Bun\Core\Storage
 */
class QueryParser
{
    /** @var array  */
    protected $operators = array(
        '='    => 'equals',
        '$ne'  => 'notEquals',
        '$gt'  => 'greater',
        '$gte' => 'greaterOrEquals',
        '$lt'  => 'lower',
        '$lte' => 'lowerOrEquals',
        '$like' => 'like',
        '$ilike' => 'iLike',
    );
    /** @var array  */
    protected $connectors = array(
        '$and' => 'and',
        '$or'  => 'or',
    );

    /**
     * @param $where
     *
     * @return array
     */
    public function parseWhere($where)
    {
        $clauses = $this->parseByIdWhere($where);
        if (count($clauses) === 0) {
            foreach ($where as $key => $clause) {
                if (!array_key_exists($key, $this->connectors)) {
                    $clauses[] = $this->parseOperandClause($key, $clause);
                }
                else {
                    $clauses[] = $this->parseConnectorClause($key, $clause);
                }
            }
        }

        return $clauses;
    }

    /**
     * @param $where
     *
     * @return array
     *
     * @throws QueryParseException
     */
    protected function parseByIdWhere($where)
    {
        if (count($where) === 1) {
            if (array_key_exists('id', $where) && !is_array($where['id'])) {
                return array(
                    '$and' => array(
                        new QueryClause('id', $this->operators['='], $where['id'])
                    )
                );
            }
            elseif (array_key_exists('id', $where) && is_array($where['id'])) {
                foreach ($where['id'] as $operator => $operand2) {
                    if (array_key_exists($operator, $this->operators)) {
                        return array(
                            '$and' => array(
                                new QueryClause('id', $this->operators[$operator], $operand2)
                            )
                        );
                    }
                    elseif (array_key_exists($operator, $this->connectors)) {
                        return array();
                    }

                    throw new QueryParseException('Unable to parse operator: ' . $operator);
                }
            }
        }

        return array();
    }

    /**
     * @param $clauses
     * @param array $data
     *
     * @return bool|mixed
     */
    public function applyClauses($clauses, $data = array())
    {
        $true = true;
        foreach ($clauses as $connector => $queries) {
            $connector = !is_numeric($connector) ?
                $connector :
                '$and';
            $trueBlock = false;
            foreach ($queries as $connector2 => $query) {
                if ($query instanceof QueryClause) {
                    $trueBlock = isset($data[$query->getOperandName()]) ?
                        $query->apply($data[$query->getOperandName()]) :
                        $query->apply(null);
                }
                else {
                    $trueBlock = $this->applyClauses(array($connector2 => $query), $data);
                }
                if ($connector === '$and' && !$trueBlock) {
                    break 1;
                }
                elseif ($connector === '$or' && !$trueBlock) {
                    break 1;
                }
            }
            $true = $trueBlock;
            if (!$trueBlock) {
                break;
            }
        }

        return $true;
    }

    /**
     * @param $operand
     * @param $clause
     *
     * @return array
     *
     * @throws QueryParseException
     */
    protected function parseOperandClause($operand, $clause)
    {
        $queries = array();
        if (!is_array($clause)) {
            $queries[] = new QueryClause(
                $operand,
                $this->operators['='],
                $clause
            );
        }
        elseif($this->isAssoc($clause)) {
            foreach ($clause as $key => $val) {
                if (array_key_exists($key, $this->connectors)) {
                    $queries[] = $this->parseConnectorClause($key, $val, $operand);
                }
                elseif (array_key_exists($key, $this->operators)) {
                    $queries[] = new QueryClause(
                        $operand,
                        $this->operators[$key],
                        $val
                    );
                }
                else {
                    throw new QueryParseException('Unable to parse operator ' . $key);
                }
            }
        }
        else {
            $queries[] = new QueryClause($operand, $this->operators['='], $clause);
        }

        return array(
            '$and' => $queries
        );
    }

    /**
     * @param $connector
     * @param $clauses
     * @param null $operand
     *
     * @return array
     */
    protected function parseConnectorClause($connector, $clauses, $operand = null)
    {
        $queries = array();
        foreach ($clauses as $key => $val) {
            if (array_key_exists($key, $this->connectors)) {
                $queries[] = $this->parseConnectorClause($key, $val, $operand);
            }
            elseif (array_key_exists($key, $this->operators)) {
                $queries[] = new QueryClause($operand, $this->operators[$key], $val);
            }
            else {
                $queries[] = $this->parseOperandClause(
                    ($operand !== null ? $operand : $key),
                    $val
                );
            }
        }

        return array($connector => $queries);
    }

    /**
     * @param $arr
     * @return bool
     */
    protected function isAssoc($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}