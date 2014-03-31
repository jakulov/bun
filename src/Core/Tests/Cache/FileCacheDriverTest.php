<?php
namespace Bun\Core\Tests\Cache;

use Bun\Core\Application;
use Bun\Core\Cache\FileCacheDriver;

/**
 * Class FileCacheDriverTest
 *
 * @package Bun\Core\Tests\Cache
 */
class FileCacheDriverTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $configMock = $this->getConfigMock();

        $driver = new FileCacheDriver();
        $driver->setConfig($configMock);

        $driver->set('test', 'test', 60);

        $this->assertEquals('test', $driver->get('test'), 'Cache value not equals "test"');
    }

    public function testDelete()
    {
        $configMock = $this->getConfigMock();

        $driver = new FileCacheDriver();
        $driver->setConfig($configMock);

        $driver->set('test', 'test', 60);
        $driver->delete('test');

        $this->assertEquals(null, $driver->get('test'), 'Cache value not deleted "test"');
    }

    public function testClear()
    {
        $configMock = $this->getConfigMock();

        $driver = new FileCacheDriver();
        $driver->setConfig($configMock);

        $driver->set('test', 'test', 60);
        $driver->clear();

        $this->assertEquals(null, $driver->get('test'), 'Cache value not cleared "test"');
    }

    public function testClearWithNamespace()
    {
        $configMock = $this->getConfigMock();

        $driver = new FileCacheDriver();
        $driver->setConfig($configMock);

        $driver->set('test1', 'test', 60, 'ns');
        $driver->set('test2', 'test', 60);

        $driver->clear('ns');

        $this->assertEquals(null, $driver->get('test1'), 'Cache namaspace not cleared "test1"');
        $this->assertEquals('test', $driver->get('test2'), 'Cache namespace cleared another value "test2"');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getConfigMock()
    {
        $app = new Application(ENV);

        $mockBuilder = $this->getMockBuilder('Bun\Core\Config\ApplicationConfig');
        $mockBuilder->setConstructorArgs(array($app));

        return $mockBuilder->getMock();
    }
}