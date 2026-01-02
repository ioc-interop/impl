<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;
use stdClass;

class PublicContainerTest extends \PHPUnit\Framework\TestCase
{
    protected PublicContainer $ioc;

    protected function setUp() : void
    {
    }

    public function testGetService() : void
    {
        $name = stdClass::class;
        $instance = new stdClass();
        $ioc = new PublicContainer();
        $this->assertFalse($ioc->hasService($name));
        $ioc->setServiceInstance($name, $instance);
        $this->assertTrue($ioc->hasService($name));
        $actual = $ioc->getService($name);
        $this->assertSame($instance, $actual);
        $again = $ioc->getService($name);
        $this->assertSame($actual, $again);
    }

    public function testGetService_aliased() : void
    {
        $name = 'foo';
        $alias = stdClass::class;
        $instance = new stdClass();
        $ioc = new PublicContainer();
        $this->assertFalse($ioc->hasService($name));
        $ioc->setServiceAlias($name, $alias);
        $this->assertFalse($ioc->hasService($name));
        $ioc->setServiceInstance($alias, $instance);
        $this->assertTrue($ioc->hasService($name));
        $actual = $ioc->getService($name);
        $this->assertSame($instance, $actual);
        $again = $ioc->getService($name);
        $this->assertSame($actual, $again);
    }

    public function testGetService_new() : void
    {
        $name = stdClass::class;
        $factory = fn (IocContainer $ioc) : stdClass => new stdClass();
        $ioc = new PublicContainer();
        $this->assertFalse($ioc->hasService($name));
        $ioc->setServiceFactory($name, $factory);
        $this->assertTrue($ioc->hasService($name));
        $actual = $ioc->getService($name);
        $this->assertInstanceOf($name, $actual);
        $again = $ioc->getService($name);
        $this->assertSame($actual, $again);
    }

    public function testGetService_aliasedNew() : void
    {
        $name = 'foo';
        $alias = stdClass::class;
        $factory = fn (IocContainer $ioc) : stdClass => new stdClass();
        $ioc = new PublicContainer();
        $this->assertFalse($ioc->hasService($name));
        $ioc->setServiceAlias($name, $alias);
        $this->assertFalse($ioc->hasService($name));
        $ioc->setServiceFactory($alias, $factory);
        $this->assertTrue($ioc->hasService($name));
        $actual = $ioc->getService($name);
        $this->assertInstanceOf($alias, $actual);
        $again = $ioc->getService($name);
        $this->assertSame($actual, $again);
    }

    public function testNewService() : void
    {
        $name = stdClass::class;
        $factory = fn (IocContainer $ioc) : stdClass => new stdClass();
        $ioc = new PublicContainer();
        $this->assertFalse($ioc->hasService($name));
        $ioc->setServiceFactory($name, $factory);
        $this->assertTrue($ioc->hasService($name));
        $actual = $ioc->newService($name);
        $this->assertInstanceOf($name, $actual);
        $again = $ioc->newService($name);
        $this->assertNotSame($actual, $again);
    }

    public function testNewService_aliased() : void
    {
        $name = 'foo';
        $alias = stdClass::class;
        $factory = fn (IocContainer $ioc) : stdClass => new stdClass();
        $ioc = new PublicContainer();
        $this->assertFalse($ioc->hasService($name));
        $ioc->setServiceAlias($name, $alias);
        $this->assertFalse($ioc->hasService($name));
        $ioc->setServiceFactory($alias, $factory);
        $this->assertTrue($ioc->hasService($name));
        $actual = $ioc->newService($name);
        $this->assertInstanceOf($alias, $actual);
        $again = $ioc->newService($name);
        $this->assertNotSame($actual, $again);
    }
}
