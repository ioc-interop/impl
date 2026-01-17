<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use stdClass;
use IocInterop\Interface\IocServiceBuilder;

class ServicesTest extends \PHPUnit\Framework\TestCase
{
    protected Services $services;

    public function testServiceInstance() : void
    {
        $name = stdClass::class;
        $instance = new stdClass();
        $services = new Services();
        $this->assertFalse($services->hasServiceInstance($name));
        $services->setServiceInstance($name, $instance);
        $this->assertTrue($services->hasServiceInstance($name));
        $actual = $services->getServiceInstance($name);
        $this->assertSame($instance, $actual);
        $again = $services->getServiceInstance($name);
        $this->assertSame($actual, $again);
        $services->unsetServiceInstance($name);
        $this->assertFalse($services->hasServiceInstance($name));
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage("No shared instance for '{$name}'.");
        $services->getServiceInstance($name);
    }

    public function testServiceBuilder() : void
    {
        $name = stdClass::class;
        $services = new Services();
        $this->assertFalse($services->hasServiceBuilder($name));
        $builder = $services->newServiceBuilder($name);
        $services->setServiceBuilder($name, $builder);
        $this->assertTrue($services->hasServiceBuilder($name));
        $actual = $services->getServiceBuilder($name);
        $this->assertSame($builder, $actual);
        $again = $services->getServiceBuilder($name);
        $this->assertSame($actual, $again);
        $services->unsetServiceBuilder($name);
        $this->assertFalse($services->hasServiceBuilder($name));
    }

    public function testServiceBuilder_implicitNewAndSet() : void
    {
        $name = stdClass::class;
        $services = new Services();
        $this->assertFalse($services->hasServiceBuilder($name));
        $actual = $services->getServiceBuilder($name);
        $this->assertInstanceOf(IocServiceBuilder::class, $actual);
        $this->assertTrue($services->hasServiceBuilder($name));
        $again = $services->getServiceBuilder($name);
        $this->assertSame($actual, $again);
    }

    public function testServiceAlias() : void
    {
        $name = 'foo.bar';
        $alias = stdClass::class;
        $services = new Services();
        $this->assertFalse($services->hasServiceAlias($name));
        $services->setServiceAlias($name, $alias);
        $this->assertTrue($services->hasServiceAlias($name));
        $actual = $services->getServiceAlias($name);
        $this->assertSame($alias, $actual);
        $services->unsetServiceAlias($name);
        $this->assertFalse($services->hasServiceAlias($name));
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage("No alias for '{$name}'.");
        $services->getServiceAlias($name);

        // recursive aliasing
        $services->setServiceAlias('bar.baz', 'foo.bar');
        $actual = $services->getServiceAlias('bar.baz');
        $this->assertSame($alias, $actual);
    }

    public function testServiceAlias_circular() : void
    {
        $services = new Services();
        $services->setServiceAlias('foo', 'bar');
        $services->setServiceAlias('bar', 'baz');
        $services->setServiceAlias('baz', 'dib');

        $this->expectException(ContainerException::class);
        $services->setServiceAlias('dib', 'foo');
    }
}
