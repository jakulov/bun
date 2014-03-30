<?php
namespace Bun\Logger;

use Bun\Core\Config\ConfigAwareInterface;
use Bun\Core\Config\ConfigInterface;
use Bun\Core\File\File;

/**
 * Class BunLogger
 *
 * @package Bun\Logger
 */
class BunLogger implements LoggerInterface, ConfigAwareInterface
{
    const MAX_LOGGER_CALLS = 10000;
    /** @var array */
    protected $levelMsg = array(
        self::LOG_LEVEL_DEBUG   => 'DEBUG',
        self::LOG_LEVEL_INFO    => 'INFO',
        self::LOG_LEVEL_WARNING => 'WARNING',
        self::LOG_LEVEL_ERROR   => 'ERROR',
    );
    /** @var ConfigInterface */
    protected $config;
    /** @var string */
    protected $logDir = 'log';
    /** @var int */
    protected $logLevel = self::LOG_LEVEL_INFO;
    /** @var bool */
    protected $useBuffer = true;
    /** @var string|null */
    protected $appPrefix = null;
    /** Max log file size Kb @var int */
    protected $maxLogSize = 100000;
    /** @var array */
    protected $logBuffer = array();
    /** Store logs for this time @var int */
    protected $logOutDated = 29376000;
    /** Max log calls @var int */
    protected $loggerCalls = 0;
    /** @var array */
    protected $loggers = array();

    /**
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        $this->init();
    }

    /**
     * Init Logger
     */
    protected function init()
    {
        $loggerConfig = $this->config->get('logger');
        $this->logDir = isset($loggerConfig['dir']) ? $loggerConfig['dir'] : $this->logDir;
        $this->logLevel = isset($loggerConfig['level']) ? $loggerConfig['level'] : $this->logLevel;
        $this->useBuffer = isset($loggerConfig['buffer']) ? $loggerConfig['buffer'] : $this->useBuffer;
        $this->appPrefix = isset($loggerConfig['prefix']) ? $loggerConfig['prefix'] : $this->appPrefix;
        $this->maxLogSize = isset($loggerConfig['max_size']) ? $loggerConfig['max_size'] : $this->maxLogSize;
        $this->logOutDated = isset($loggerConfig['outdated']) ? $loggerConfig['outdated'] : $this->logOutDated;
        $this->loggers = isset($loggerConfig['loggers']) ? $loggerConfig['loggers'] : $this->loggers;

        $logDir = $this->getLogDir();
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        else {
            // rotating logs
            $this->rotate();
        }
    }

    /**
     * Rotates log files and removes outdated
     */
    public function rotate()
    {
        $logDir = $this->getLogDir();
        $logDirHandler = opendir($logDir);

        $toArchive = array();
        while ($logFile = readdir($logDirHandler)) {
            $logFileName = $logDir . DIRECTORY_SEPARATOR . $logFile;
            if (is_file($logFileName)) {
                $logNameParts = explode('.', $logFile);
                if (count($logNameParts) === 3) {
                    $logModified = filemtime($logFileName);
                    if ((time() - $logModified) > $this->logOutDated) {
                        $this->log(
                            'Removed log file ' . $logFile . ' as outdated (' . date('Y-m-d', $logModified) . ')',
                            self::LOG_LEVEL_INFO
                        );
                        unlink($logFileName);
                    }
                    else {
                        if ($logNameParts[2] === 'log') {
                            $toArchive[$logNameParts[0]] = $logFileName;
                        }
                    }
                }
            }
        }

        $logDirHandler = opendir($logDir);
        $needArchive = array();
        while ($logFile = readdir($logDirHandler)) {
            $logFileName = $logDir . DIRECTORY_SEPARATOR . $logFile;
            if (is_file($logFileName)) {
                $logNameParts = explode('.', $logFile);
                if (count($logNameParts) === 2) {
                    if (filesize($logFileName) >= $this->maxLogSize) {
                        $newLogFileName = $logNameParts[0] . '.' . date('YmdHi') . '.' . $logNameParts[1];
                        rename($logFileName, $logDir . DIRECTORY_SEPARATOR . $newLogFileName);
                        $needArchive[$logNameParts[0]] = true;
                    }
                }
            }
        }

        foreach ($needArchive as $logName => $true) {
            if (isset($toArchive[$logName])) {
                $logFileName = $toArchive[$logName];
                if (is_file($logFileName)) {
                    $newLogFileName = str_replace('.log', '.gz', $logFileName);
                    $gzFile = gzopen($newLogFileName, 'w9');
                    gzwrite($gzFile, file_get_contents($logFileName));
                    gzclose($gzFile);
                    unlink($logFileName);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return VAR_DIR . DIRECTORY_SEPARATOR . $this->logDir .
        ($this->appPrefix !== null ?
            DIRECTORY_SEPARATOR . $this->appPrefix :
            ''
        );
    }

    /**
     * @param string $loggerName
     * @return string
     */
    protected function getLogFileName($loggerName = self::DEFAULT_LOGGER_NAME)
    {
        return $this->getLogDir() . DIRECTORY_SEPARATOR . $loggerName . '.log';
    }

    /**
     * @param $msg
     * @param int $level
     * @param string $name
     */
    public function log($msg, $level = self::LOG_LEVEL_INFO, $name = self::DEFAULT_LOGGER_NAME)
    {
        if (!($level & $this->getLoggerLevel($name))) {
            return;
        }

        $this->loggerCalls++;
        if ($this->loggerCalls >= self::MAX_LOGGER_CALLS) {
            error_log('WARNING: Max logger calls exceeded: ' . self::MAX_LOGGER_CALLS, LOG_ALERT);

            return;
        }

        if ($this->useBuffer) {
            if (!isset($this->logBuffer[$name])) {
                $this->logBuffer[$name] = '';
            }
            $this->logBuffer[$name] .= $this->composeLogMsg($msg, $level);

            return;
        }

        File::append($this->getLogFileName($name), $this->composeLogMsg($msg, $level));
    }

    /**
     * @param $loggerName
     * @return int
     */
    public function getLoggerLevel($loggerName)
    {
        if(isset($this->loggers[$loggerName]) && isset($this->loggers[$loggerName]['level'])) {
            return (int)$this->loggers[$loggerName]['level'];
        }

        return $this->logLevel;
    }

    /**
     * @param $msg
     * @param $level
     * @return string
     */
    protected function composeLogMsg($msg, $level)
    {
        return date('Y-m-d H:i:s') . "\t" . $this->levelMsg[$level] . "\t" . $msg . "\n";
    }

    /**
     * @return bool
     */
    public function flushLogs()
    {
        if ($this->useBuffer) {
            $flushedLogs = array();
            foreach ($this->logBuffer as $loggerName => $logMsg) {
                $flushed = File::append($this->getLogFileName($loggerName), $logMsg);
                if($flushed) {
                    $flushedLogs[] = $loggerName;
                }
            }
            foreach($flushedLogs as $loggerName) {
                $this->logBuffer[$loggerName] = '';
            }
        }

        return true;
    }

    public function __destruct()
    {
        $this->flushLogs();
    }
}