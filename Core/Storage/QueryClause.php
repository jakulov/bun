<?php
namespace Bun\Core\Storage;

/**
 * Class QueryClause
 *
 * @package Bun\Core\Storage
 */
class QueryClause
{
    protected $operand1;
    protected $operand2;
    protected $operator;

    /**
     * @param $o1
     * @param $operation
     * @param null $o2
     */
    public function __construct($o1, $operation, $o2 = null)
    {
        $this->operand1 = $o1;
        $this->operand2 = $o2;
        $this->operator = $operation;
    }

    /**
     * @return mixed
     */
    public function getOperand2()
    {
        return $this->operand2;
    }

    /**
     * @return mixed
     */
    public function getOperandName()
    {
        return $this->operand1;
    }

    /**
     * @param $value
     * @return bool
     */
    public function apply($value)
    {
        return call_user_func_array(
            array($this, $this->operator),
            array($value, $this->operand2)
        );
    }

    /**
     * @param $o1
     * @param $o2
     * @return bool
     */
    protected function equals($o1, $o2)
    {
        if (is_array($o2) && !is_array($o1)) {
            return in_array($o1, $o2);
        }

        return $o1 === $o2;
    }

    /**
     * @param $o1
     * @param $o2
     * @return bool
     */
    protected function greater($o1, $o2)
    {
        return $o1 > $o2;
    }

    /**
     * @param $o1
     * @param $o2
     * @return bool
     */
    protected function greaterOrEquals($o1, $o2)
    {
        return $this->greater($o1, $o2) || $this->equals($o1, $o2);
    }

    /**
     * @param $o1
     * @param $o2
     * @return bool
     */
    protected function lower($o1, $o2)
    {
        return $o1 < $o2;
    }

    /**
     * @param $o1
     * @param $o2
     * @return bool
     */
    protected function lowerOrEquals($o1, $o2)
    {
        return $this->lower($o1, $o2) || $this->equals($o1, $o2);
    }

    /**
     * @param $o1
     * @param $o2
     * @return bool
     */
    protected function notEquals($o1, $o2)
    {
        return $o1 !== $o2;
    }

    /**
     * @param null $operator
     *
     * @return bool
     */
    public function isByIdClause($operator = null)
    {
        return (
            $this->operand1 === 'id' &&
            ($this->operator === $operator || $operator === null)
        );
    }

    /**
     * @param $o1
     * @param $o2
     * @return bool
     */
    public function like($o1, $o2)
    {
        $needle = str_replace('%', '', $o1);
        if (strpos($o1, '%') === false) {
            $haystack = explode(' ', $o2);

            return in_array($needle, $haystack);
        }
        elseif (strpos($o1, '%') === 0 && strpos($o1, '%', 1) === false) {
            $needlePos = strpos($o2, $needle);

            return ($needlePos !== false && substr($o2, $needlePos + strlen($needlePos), 1) === ' ');
        }
        else {
            return strpos($o2, $needle) !== false;
        }
    }

    /**
     * @param $o1
     * @param $o2
     * @return bool
     */
    public function iLike($o1, $o2)
    {
        $needle = str_replace('%', '', $o2);
        if (stripos($o2, '%') === false) {
            $haystack = explode(' ', $o1);
            $haystack = array_map('strtolower', $haystack);

            return in_array(strtolower($needle), $haystack);
        }
        elseif (strpos($o2, '%') === 0 && strpos($o2, '%', 1) === false) {
            $needlePos = stripos($o1, $needle);

            return ($needlePos !== false && substr($o1, $needlePos + strlen($needlePos), 1) === ' ');
        }
        else {
            return stripos($o1, $needle) !== false;
        }
    }
}