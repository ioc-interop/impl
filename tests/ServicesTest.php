<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use stdClass;
use IocInterop\Interface\IocDefinition;

class ServicesTest extends \PHPUnit\Framework\TestCase
{
    protected Services $services;

    public function testInstance() : void
    {
        $name = stdClass::class;
        $instance = new stdClass();
        $services = new Services();
        $this->assertFalse($services->hasInstance($name));
        $services->setInstance($name, $instance);
        $this->assertTrue($services->hasInstance($name));
        $actual = $services->getInstance($name);
        $this->assertSame($instance, $actual);
        $again = $services->getInstance($name);
        $this->assertSame($actual, $again);
        $services->unsetInstance($name);
        $this->assertFalse($services->hasInstance($name));
        $this->expectException(IocException::class);
        $this->expectExceptionMessage("No shared instance for '{$name}'.");
        $services->getInstance($name);
    }

    public function testDefinition() : void
    {
        $name = stdClass::class;
        $services = new Services();
        $this->assertFalse($services->hasDefinition($name));
        $builder = $services->newDefinition($name);
        $services->setDefinition($name, $builder);
        $this->assertTrue($services->hasDefinition($name));
        $actual = $services->getDefinition($name);
        $this->assertSame($builder, $actual);
        $again = $services->getDefinition($name);
        $this->assertSame($actual, $again);
        $services->unsetDefinition($name);
        $this->assertFalse($services->hasDefinition($name));
    }

    public function testDefinition_implicitNewAndSet() : void
    {
        $name = stdClass::class;
        $services = new Services();
        $this->assertFalse($services->hasDefinition($name));
        $actual = $services->getDefinition($name);
        $this->assertInstanceOf(IocDefinition::class, $actual);
        $this->assertTrue($services->hasDefinition($name));
        $again = $services->getDefinition($name);
        $this->assertSame($actual, $again);
    }

    public function testAlias() : void
    {
        $name = 'foo.bar';
        $alias = stdClass::class;
        $services = new Services();
        $this->assertFalse($services->hasAlias($name));
        $services->setAlias($name, $alias);
        $this->assertTrue($services->hasAlias($name));
        $actual = $services->getAlias($name);
        $this->assertSame($alias, $actual);
        $services->unsetAlias($name);
        $this->assertFalse($services->hasAlias($name));
        $this->expectException(IocException::class);
        $this->expectExceptionMessage("No alias for '{$name}'.");
        $services->getAlias($name);

        // recursive aliasing
        $services->setAlias('bar.baz', 'foo.bar');
        $actual = $services->getAlias('bar.baz');
        $this->assertSame($alias, $actual);
    }

    public function testAlias_circular() : void
    {
        $services = new Services();
        $services->setAlias('foo', 'bar');
        $services->setAlias('bar', 'baz');
        $services->setAlias('baz', 'dib');

        $this->expectException(IocException::class);
        $services->setAlias('dib', 'foo');
    }
}
