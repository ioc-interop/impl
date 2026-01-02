<?php
declare(strict_types=1);

namespace IocInterop\Impl;

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
        $services->setServiceInstance($name, $instance);
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
        $services->setServiceAlias($name, $alias);
        $services->setServiceInstance($alias, $instance);
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
        $services->setServiceFactory($name, $factory);
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
        $services->setServiceAlias($name, $alias);
        $services->setServiceFactory($alias, $factory);
        $ioc = new ProtectedContainer($services);

        // act & assert
        $actual = $ioc->getService($name);
        $this->assertInstanceOf($alias, $actual);
        $again = $ioc->getService($name);
        $this->assertSame($actual, $again);
    }

    public function testNewService() : void
    {
        // assemble
        $name = stdClass::class;
        $factory = fn (IocContainer $ioc) : stdClass => new stdClass();
        $services = new Services();
        $services->setServiceFactory($name, $factory);
        $ioc = new ProtectedContainer($services);

        // act & assert
        $actual = $ioc->newService($name);
        $this->assertInstanceOf($name, $actual);
        $again = $ioc->newService($name);
        $this->assertNotSame($actual, $again);
    }

    public function testNewService_aliased() : void
    {
        // assemble
        $name = 'foo';
        $alias = stdClass::class;
        $factory = fn (IocContainer $ioc) : stdClass => new stdClass();
        $services = new Services();
        $services->setServiceAlias($name, $alias);
        $services->setServiceFactory($alias, $factory);
        $ioc = new ProtectedContainer($services);

        // act & assert
        $actual = $ioc->newService($name);
        $this->assertInstanceOf($alias, $actual);
        $again = $ioc->newService($name);
        $this->assertNotSame($actual, $again);
    }

    public function testHasService() : void
    {
        $services = new Services();
        $services->setServiceInstance('foo', new stdClass());
        $services->setServiceFactory('bar', fn (IocContainer $ioc) => new stdClass());
        $services->setServiceAlias('baz', 'foo');
        $ioc = new ProtectedContainer($services);

        $this->assertTrue($ioc->hasService('foo'));
        $this->assertTrue($ioc->hasService('bar'));
        $this->assertTrue($ioc->hasService('baz'));
        $this->assertFalse($ioc->hasService('dib'));
    }
}
