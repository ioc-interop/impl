<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Impl\Fake\FakeServiceCircularFoo;;
use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocDefinition;
use stdClass;

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testGetService() : void
    {
        // assemble
        $serviceName = stdClass::class;
        $instance = new stdClass();
        $ioc = new Container();
        $ioc->setInstance($serviceName, $instance);

        // act & assert
        $actual = $ioc->getService($serviceName);
        $this->assertSame($instance, $actual);
        $again = $ioc->getService($serviceName);
        $this->assertSame($actual, $again);
    }

    public function testGetService_aliased() : void
    {
        // assemble
        $serviceName = 'foo';
        $alias = stdClass::class;
        $instance = new stdClass();
        $ioc = new Container();
        $ioc->setAlias($serviceName, $alias);
        $ioc->setInstance($alias, $instance);

        // act & assert
        $actual = $ioc->getService($serviceName);
        $this->assertSame($instance, $actual);
        $again = $ioc->getService($serviceName);
        $this->assertSame($actual, $again);
    }

    public function testGetService_new() : void
    {
        // assemble
        $serviceName = stdClass::class;
        $factory = fn (IocContainer $ioc) : stdClass => new stdClass();
        $ioc = new Container();
        $ioc->getDefinition($serviceName)->setFactory($factory);

        // act & assert
        $actual = $ioc->getService($serviceName);
        $this->assertInstanceOf($serviceName, $actual);
        $again = $ioc->getService($serviceName);
        $this->assertSame($actual, $again);
    }

    public function testGetService_aliasedNew() : void
    {
        // assemble
        $serviceName = 'foo';
        $alias = stdClass::class;
        $factory = fn (IocContainer $ioc) : stdClass => new stdClass();
        $ioc = new Container();
        $ioc->setAlias($serviceName, $alias);
        $ioc->getDefinition($alias)->setFactory($factory);

        // act & assert
        $actual = $ioc->getService($serviceName);
        $this->assertInstanceOf($alias, $actual);
        $again = $ioc->getService($serviceName);
        $this->assertSame($actual, $again);
    }

    public function testHasService() : void
    {
        $ioc = new Container();
        $ioc->setInstance('foo', new stdClass());
        $ioc->getDefinition('bar')->setFactory(fn (IocContainer $ioc) => new stdClass());
        $ioc->setAlias('baz', 'foo');

        $this->assertTrue($ioc->hasService('foo'));
        $this->assertTrue($ioc->hasService('bar'));
        $this->assertTrue($ioc->hasService('baz'));
        $this->assertFalse($ioc->hasService('dib'));
    }

    public function testCircularPrevention() : void
    {
        $ioc = new Container();
        $this->expectException(IocException::class);
        $this->expectExceptionMessage("Circular dependency: IocInterop\Impl\Fake\FakeServiceCircularFoo, IocInterop\Impl\Fake\FakeServiceCircularBar, IocInterop\Impl\Fake\FakeServiceCircularFoo");
        $ioc->getService(FakeServiceCircularFoo::class);
    }

    public function testInstance() : void
    {
        $name = stdClass::class;
        $instance = new stdClass();
        $ioc = new Container();
        $this->assertFalse($ioc->hasInstance($name));
        $ioc->setInstance($name, $instance);
        $this->assertTrue($ioc->hasInstance($name));
        $actual = $ioc->getInstance($name);
        $this->assertSame($instance, $actual);
        $again = $ioc->getInstance($name);
        $this->assertSame($actual, $again);
        $ioc->unsetInstance($name);
        $this->assertFalse($ioc->hasInstance($name));
        $this->expectException(IocException::class);
        $this->expectExceptionMessage("No shared instance for '{$name}'.");
        $ioc->getInstance($name);
    }

    public function testDefinition() : void
    {
        $name = stdClass::class;
        $ioc = new Container();
        $this->assertFalse($ioc->hasDefinition($name));
        $builder = $ioc->newDefinition($name);
        $ioc->setDefinition($name, $builder);
        $this->assertTrue($ioc->hasDefinition($name));
        $actual = $ioc->getDefinition($name);
        $this->assertSame($builder, $actual);
        $again = $ioc->getDefinition($name);
        $this->assertSame($actual, $again);
        $ioc->unsetDefinition($name);
        $this->assertFalse($ioc->hasDefinition($name));
    }

    public function testDefinition_implicitNewAndSet() : void
    {
        $name = stdClass::class;
        $ioc = new Container();
        $this->assertFalse($ioc->hasDefinition($name));
        $actual = $ioc->getDefinition($name);
        $this->assertInstanceOf(IocDefinition::class, $actual);
        $this->assertTrue($ioc->hasDefinition($name));
        $again = $ioc->getDefinition($name);
        $this->assertSame($actual, $again);
    }

    public function testAlias() : void
    {
        $name = 'foo.bar';
        $alias = stdClass::class;
        $ioc = new Container();
        $this->assertFalse($ioc->hasAlias($name));
        $ioc->setAlias($name, $alias);
        $this->assertTrue($ioc->hasAlias($name));
        $actual = $ioc->getAlias($name);
        $this->assertSame($alias, $actual);
        $ioc->unsetAlias($name);
        $this->assertFalse($ioc->hasAlias($name));
        $this->expectException(IocException::class);
        $this->expectExceptionMessage("No alias for '{$name}'.");
        $ioc->getAlias($name);

        // recursive aliasing
        $ioc->setAlias('bar.baz', 'foo.bar');
        $actual = $ioc->getAlias('bar.baz');
        $this->assertSame($alias, $actual);
    }

    public function testAlias_circular() : void
    {
        $ioc = new Container();
        $ioc->setAlias('foo', 'bar');
        $ioc->setAlias('bar', 'baz');
        $ioc->setAlias('baz', 'dib');

        $this->expectException(IocException::class);
        $ioc->setAlias('dib', 'foo');
    }
}
