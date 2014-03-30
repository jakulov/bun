<?php
namespace Bun\Core\Config\Test\Config;

use Bun\Core\Application;
use Bun\Core\Config\ApplicationConfig;

/**
 * Class ApplicationConfigTest
 *
 * @package Bun\Core\Config\Test\Config
 */
class ApplicationConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * TODO: add ignore config
     * внимание - привязан к TestConfig
     */
    public function testGet()
    {
        $app = new Application(ENV);

        $config = new ApplicationConfig($app);

        $this->assertEquals('test1', $config->get('test.test1'), 'test1 not equals');
        $this->assertEquals('test1', $config->get('test.test2.test1'), 'test2 not equals');
        //$this->assertEquals(\FConfig::get('test'), $config->get('test.test3'), 'test3 not equals FConfig::get');
    }

    public function testGetBunModules()
    {
        $modules = array();
        $dir = opendir(LIB_DIR . '/Bun');
        while ($f = readdir($dir)) {
            if ($f !== '.' && $f !== '..') {
                if (is_dir(LIB_DIR . '/Bun/' . $f)) {
                    $modules[] = $f;
                }
            }
        }

        $app = new Application(ENV);
        $config = new ApplicationConfig($app);

        $this->assertEquals($modules, $config->getBunModules());
    }

    public function testGetApplication()
    {
        $apps = array();
        $dir = opendir(SRC_DIR);
        $lastApp = '';
        while ($f = readdir($dir)) {
            if ($f !== '.' && $f !== '..') {
                if (is_dir(SRC_DIR . '/' . $f)) {
                    $class = $f .'\\Application';
                    $bunApp = new $class;
                    $apps[$f] = $bunApp;
                    $lastApp = $f;
                }
            }
        }

        $app = new Application(ENV);
        $config = new ApplicationConfig($app);

        $this->assertEquals($apps[$lastApp], $config->getApplication($lastApp));
    }

    public function testGetApplicationsList()
    {
        $apps = array();
        $dir = opendir(SRC_DIR);
        while ($f = readdir($dir)) {
            if ($f !== '.' && $f !== '..') {
                if (is_dir(SRC_DIR . '/' . $f)) {
                    $class = $f .'\\Application';
                    $bunApp = new $class;
                    $apps[$f] = $bunApp;
                }
            }
        }

        $app = new Application(ENV);
        $config = new ApplicationConfig($app);

        $this->assertEquals($apps, $config->getApplicationsList());
    }
}