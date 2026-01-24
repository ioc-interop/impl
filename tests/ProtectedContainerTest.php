<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Impl\Fake\FakeServiceCircularFoo;;
use IocInterop\Interface\IocContainer;
use stdClass;

class ProtectedContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testGetService() : void
    {
        // assemble
        $name = stdClass::class;
        $instance = new stdClass();
        $services = new Services();
        $services->setInstance($name, $instance);
        $ioc = new ProtectedContainer($services);

        // act & assert
        $actual = $ioc->getService($name);
        $this->assertSame($instance, $actual);
        $again = $ioc->getService($name);
        $this->assertSame($actual, $again);
    }

    public function testGetService_aliased() : void
    {
        // assemble
        $name = 'foo';
        $alias = stdClass::class;
        $instance = new stdClass();
        $services = new Services();
        $services->setAlias($name, $alias);
        $services->setInstance($alias, $instance);
        $ioc = new ProtectedContainer($services);

        // act & assert
        $actual = $ioc->getService($name);
        $this->assertSame($instance, $actual);
        $again = $ioc->getService($name);
        $this->assertSame($actual, $again);
    }

    public function testGetService_new() : void
    {
        // assemble
        $name = stdClass::class;
        $factory = fn (IocContainer $ioc) : stdClass => new stdClass();
        $services = new Services();
        $services->getDefinition($name)->setFactory($factory);
        $ioc = new ProtectedContainer($services);

        // act & assert
        $actual = $ioc->getService($name);
        $this->assertInstanceOf($name, $actual);
        $again = $ioc->getService($name);
        $this->assertSame($actual, $again);
    }

    public function testGetService_aliasedNew() : void
    {
        // assemble
        $name = 'foo';
        $alias = stdClass::class;
        $factory = fn (IocContainer $ioc) : stdClass => new stdClass();
        $services = new Services();
        $services->setAlias($name, $alias);
        $services->getDefinition($alias)->setFactory($factory);
        $ioc = new ProtectedContainer($services);

        // act & assert
        $actual = $ioc->getService($name);
        $this->assertInstanceOf($alias, $actual);
        $again = $ioc->getService($name);
        $this->assertSame($actual, $again);
    }

    public function testHasService() : void
    {
        $services = new Services();
        $services->setInstance('foo', new stdClass());
        $services->getDefinition('bar')->setFactory(fn (IocContainer $ioc) => new stdClass());
        $services->setAlias('baz', 'foo');
        $ioc = new ProtectedContainer($services);

        $this->assertTrue($ioc->hasService('foo'));
        $this->assertTrue($ioc->hasService('bar'));
        $this->assertTrue($ioc->hasService('baz'));
        $this->assertFalse($ioc->hasService('dib'));
    }

    public function testCircularPrevention() : void
    {
        $ioc = new ProtectedContainer();
        $this->expectException(IocException::class);
        $this->expectExceptionMessage("Circular dependency: IocInterop\Impl\Fake\FakeServiceCircularFoo, IocInterop\Impl\Fake\FakeServiceCircularBar, IocInterop\Impl\Fake\FakeServiceCircularFoo");
        $ioc->getService(FakeServiceCircularFoo::class);
    }
}
