<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use stdClass;
use IocInterop\Interface\IocContainer;

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

    public function testServiceFactory() : void
    {
        $name = stdClass::class;
        $factory = fn (IocContainer $ioc) : stdClass => new stdClass();
        $services = new Services();
        $this->assertFalse($services->hasServiceFactory($name));
        $services->setServiceFactory($name, $factory);
        $this->assertTrue($services->hasServiceFactory($name));
        $actual = $services->getServiceFactory($name);
        $this->assertSame($factory, $actual);
        $services->unsetServiceFactory($name);
        $this->assertFalse($services->hasServiceFactory($name));
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage("No factory for '{$name}'.");
        $services->getServiceFactory($name);
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
    }
}
