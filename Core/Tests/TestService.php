<?php
namespace Bun\Core\Tests;

/**
 * Class TestService
 *
 * @package Bun\Core\Tests
 */
class TestService implements TestDependency2AwareInterface
{
    public $dependency1;
    public $dependency2;

    /**
     * @param TestDependency1 $dependency1
     */
    public function setTestDependency1(TestDependency1 $dependency1)
    {
        $this->dependency1 = $dependency1;
    }

    /**
     * @param TestDependency2 $dependency2
     */
    public function setTestDependency2(TestDependency2 $dependency2)
    {
        $this->dependency2 = $dependency2;
    }
}