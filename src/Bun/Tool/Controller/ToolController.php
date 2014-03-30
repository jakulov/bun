<?php
namespace Bun\Tool\Controller;

use Bun\Core\Controller\AbstractController;
use Bun\Tool\ConsoleResponse;
use Bun\Tool\RunTimer;

/**
 * Class ToolController
 *
 * @package Bun\Tool\Controller
 */
class ToolController extends AbstractController
{
    const NL = "\n";
    /** @var array */
    protected static $foregroundColors = array();
    /** @var array */
    protected static $backgroundColors = array();
    /** @var array */
    protected $consoleArguments = array();

    /**
     * @return \Bun\Core\Http\Response|ConsoleResponse
     */
    protected function indexAction()
    {
        self::out('');
        self::out('Welcome to Bun tools', "\n", 'light_green');
        self::out('Type bun.tool:help for list registered tools', "\n", 'cyan');
        self::out('');

        return new ConsoleResponse('OK', ConsoleResponse::RESPONSE_OK);
    }

    /**
     * @return ConsoleResponse
     */
    protected function helpAction()
    {
        $timer = new RunTimer(true);
        $toolConfig = $this->getConfig()->get('tool');
        self::out('');
        self::out('You have registered tools:', "\n", 'light_green');
        foreach (array_keys($toolConfig) as $toolName) {
            self::out("- " . $toolName, "\n", 'white');
        }

        self::out('');
        self::out('Type tool.name:help for more information', "\n\n", 'cyan');

        return new ConsoleResponse('OK', ConsoleResponse::RESPONSE_OK, $timer);
    }

    /**
     * Initialize colors
     */
    protected function init()
    {
        self::$foregroundColors['black'] = '0;30';
        self::$foregroundColors['dark_gray'] = '1;30';
        self::$foregroundColors['blue'] = '0;34';
        self::$foregroundColors['light_blue'] = '1;34';
        self::$foregroundColors['green'] = '0;32';
        self::$foregroundColors['light_green'] = '1;32';
        self::$foregroundColors['cyan'] = '0;36';
        self::$foregroundColors['light_cyan'] = '1;36';
        self::$foregroundColors['red'] = '0;31';
        self::$foregroundColors['light_red'] = '1;31';
        self::$foregroundColors['purple'] = '0;35';
        self::$foregroundColors['light_purple'] = '1;35';
        self::$foregroundColors['brown'] = '0;33';
        self::$foregroundColors['yellow'] = '1;33';
        self::$foregroundColors['light_gray'] = '0;37';
        self::$foregroundColors['white'] = '1;37';

        self::$backgroundColors['black'] = '40';
        self::$backgroundColors['red'] = '41';
        self::$backgroundColors['green'] = '42';
        self::$backgroundColors['yellow'] = '43';
        self::$backgroundColors['blue'] = '44';
        self::$backgroundColors['magenta'] = '45';
        self::$backgroundColors['cyan'] = '46';
        self::$backgroundColors['light_gray'] = '47';
    }

    /**
     * @param $string
     * @param null $foregroundColor
     * @param null $backgroundColor
     * @return string
     */
    public static function getColoredString($string, $foregroundColor = null, $backgroundColor = null)
    {
        $coloredString = "";

        if (isset(self::$foregroundColors[$foregroundColor])) {
            $coloredString .= "\033[" . self::$foregroundColors[$foregroundColor] . "m";
        }
        if (isset(self::$backgroundColors[$backgroundColor])) {
            $coloredString .= "\033[" . self::$backgroundColors[$backgroundColor] . "m";
        }

        $coloredString .= $string . "\033[0m";

        return $coloredString;
    }

    /**
     * @param $str
     * @param string $ln
     * @param null $color
     * @param null $bgColor
     */
    public static function out($str, $ln = "\n", $color = null, $bgColor = null)
    {
        echo ($color !== null || $bgColor !== null) ?
            self::getColoredString($str, $color, $bgColor) . $ln :
            $str . $ln;
    }

    /**
     * @param bool $ask
     * @return string
     */
    public static function in($ask = null)
    {
        if ($ask) {
            echo $ask;
        }

        return rtrim(fgets(STDIN), "\n");
    }

    /**
     * @param $ask
     * @param string $answer
     * @return bool
     */
    public static function promt($ask, $answer = 'Y')
    {
        $in = self::in($ask);
        if (strtoupper($in) === $answer) {
            return true;
        }

        return false;
    }

    /**
     * @param $commands
     */
    protected function outCommandsHelp($commands)
    {
        self::out('');
        foreach ($commands as $com => $desc) {
            self::out(' - ' . $com, "", 'white');
            self::out(' - ' . $desc, "\n", 'light_gray');
        }
        self::out('');
    }

    /**
     * @param $arg
     * @return bool
     */
    public function hasConsoleArgument($arg)
    {
        return (in_array($arg, array_keys($this->getConsoleArguments())));
    }

    /**
     * @return array
     */
    public function getConsoleArguments()
    {
        if (!$this->consoleArguments) {
            $args = $this->getRequest()->getConsoleArgs();
            $file = $args[0];
            array_shift($args);
            $parsed = array();
            foreach ($args as $arg) {
                $argParts = explode('=', $arg);
                $parsed[str_replace('--', '', $argParts[0])] = isset($argParts[1]) ? $argParts[1] : null;
            }
            $parsed['__file'] = $file;
            $this->consoleArguments = $parsed;
        }

        return $this->consoleArguments;
    }

    /**
     * @param $arg
     * @return null|string
     */
    public function getConsoleArgumentValue($arg)
    {
        $args = $this->getConsoleArguments();
        if (isset($args[$arg])) {
            return $args[$arg];
        }

        return null;
    }
}