<?php
namespace Bun\Logger;

/**
 * Interface LoggerInterface
 *
 * @package Bun\Logger
 */
interface LoggerInterface
{
    const DEFAULT_LOGGER_NAME = 'bun';

    const LOG_LEVEL_DEBUG = 2;
    const LOG_LEVEL_INFO = 4;
    const LOG_LEVEL_WARNING = 8;
    const LOG_LEVEL_ERROR = 16;

    /**
     * @param $msg
     * @param int $level
     * @param string $name
     * @return void
     */
    public function log($msg, $level = self::LOG_LEVEL_INFO, $name = self::DEFAULT_LOGGER_NAME);

    /**
     * @return bool
     */
    public function flushLogs();
}