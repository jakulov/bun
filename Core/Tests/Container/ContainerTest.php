<?php
namespace Bun\Core\Config\Test\Container;

use Bun\Core\Container\Container;
use Bun\Core\Tests\AbstractTest;
use Bun\Core\Tests\TestDependency1;
use Bun\Core\Tests\TestDependency2;
use Bun\Core\Tests\TestService;

/**
 * Class ContainerTest
 *
 * @package Bun\Core\Config\Test\Container
 */
class ContainerTest extends AbstractTest
{
    public function testGet()
    {
        $config = $this->getConfig($this->getApplication());

        $container = Container::getInstance($config);

        $testService = new TestService();
        $testService->setTestDependency1(new TestDependency1());
        $testService->setTestDependency2(new TestDependency2());

        $this->assertEquals(
            $testService,
            $container->get('bun.test_service'),
            'Service classes are not equals'
        );

        $this->assertEquals(
            $testService->dependency1,
            $container->get('bun.test_service')->dependency1,
            'test1 dependency not equals'
        );

        $this->assertEquals(
            $testService->dependency2,
            $container->get('bun.test_service')->dependency2,
            'test2 dependency not equals'
        );
    }
}