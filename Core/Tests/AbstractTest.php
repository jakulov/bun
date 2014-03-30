<?php
namespace Bun\Core\Tests;

use Bun\Core\Application;
use Bun\Core\Config\ApplicationConfig;

/**
 * Class AbstractTest
 *
 * @package Bun\Core\Tests
 */
abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Application
     */
    protected function getApplication()
    {
        return new Application(ENV);
    }

    /**
     * @param Application $app
     * @return ApplicationConfig
     */
    protected function getConfig(Application $app)
    {
        return new ApplicationConfig($app);
    }
}