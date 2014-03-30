<?php
namespace Bun\Tool;

/**
 * Class RunTimer
 *
 * @package Bun\Tool
 */
class RunTimer
{
    /** @var float */
    protected $start = 0;

    /**
     * @param bool $autoStart
     */
    public function __construct($autoStart = true)
    {
        if ($autoStart) {
            $this->start = microtime(true);
        }
    }

    /**
     * Starts counter
     */
    public function start()
    {
        $this->start = microtime(true);
    }

    /**
     * @return bool
     */
    public function isStarted()
    {
        return $this->start !== 0;
    }

    /**
     * @param int $roundDecimals
     * @return float
     */
    public function getRunTime($roundDecimals = 3)
    {
        return round(microtime(true) - $this->start, $roundDecimals);
    }
}