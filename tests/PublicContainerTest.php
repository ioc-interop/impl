<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;
use IocInterop\Impl\Fake\FakeServiceCircularFoo;
use stdClass;

class PublicContainerTest extends \PHPUnit\Framework\TestCase
{
    protected PublicContainer $ioc;

    protected function setUp() : void
    {
    }

    public function testGetService_instance() : void
    {
        $name = stdClass::class;
        $instance = new stdClass();
        $ioc = new PublicContainer();
        $ioc->setServiceInstance($name, $instance);
        $actual = $ioc->getService($name);
        $this->assertSame($instance, $actual);
        $again = $ioc->getService($name);
        $this->assertSame($actual, $again);
    }

    public function testGetService_aliasedInstance() : void
    {
        $name = 'foo';
        $alias = stdClass::class;
        $instance = new stdClass();
        $ioc = new PublicContainer();
        $ioc->setServiceAlias($name, $alias);
        $ioc->setServiceInstance($alias, $instance);
        $this->assertTrue($ioc->hasService($name));
        $actual = $ioc->getService($name);
        $this->assertSame($instance, $actual);
        $again = $ioc->getService($name);
        $this->assertSame($actual, $again);
    }

    public function testGetService_builder() : void
    {
        $name = stdClass::class;
        $ioc = new PublicContainer();
        $ioc->getServiceBuilder($name);
        $actual = $ioc->getService($name);
        $this->assertInstanceOf($name, $actual);
        $again = $ioc->getService($name);
        $this->assertSame($actual, $again);
    }

    public function testGetService_aliasedBuilder() : void
    {
        $name = 'foo';
        $alias = stdClass::class;
        $factory = fn (IocContainer $ioc) : stdClass => new stdClass();
        $ioc = new PublicContainer();
        $ioc->setServiceAlias($name, $alias);
        $ioc->getServiceBuilder($alias);
        $actual = $ioc->getService($name);
        $this->assertInstanceOf($alias, $actual);
        $again = $ioc->getService($name);
        $this->assertSame($actual, $again);
    }

    public function testCircularPrevention() : void
    {
        $ioc = new PublicContainer();
        $this->expectException(IocException::class);
        $this->expectExceptionMessage("Circular dependency: IocInterop\Impl\Fake\FakeServiceCircularFoo, IocInterop\Impl\Fake\FakeServiceCircularBar, IocInterop\Impl\Fake\FakeServiceCircularFoo");
        $ioc->getService(FakeServiceCircularFoo::class);
    }
}
