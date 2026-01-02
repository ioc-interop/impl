<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;
use stdClass;

class AutowiredContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testGetService() : void
    {
        // assemble
        $ioc = new AutowiredContainer();

        // act & assert
        $this->assertTrue($ioc->hasService(FakeService::class));
        $actual = $ioc->getService(FakeService::class);
        $this->assertInstanceOf(FakeService::class, $actual);
        $this->assertInstanceOf(stdClass::class, $actual->dependency);
    }

    public function testGetService_aliased() : void
    {
        // assemble
        $services = new Services();
        $services->setServiceAlias(FakeServiceInterface::class, FakeService::class);
        $ioc = new AutowiredContainer($services);

        // act & assert
        $this->assertTrue($ioc->hasService(FakeServiceInterface::class));
        $actual = $ioc->getService(FakeServiceInterface::class);
        $this->assertInstanceOf(FakeService::class, $actual);
        $this->assertInstanceOf(stdClass::class, $actual->dependency);
    }

    public function testGetService_instanceBeforeFactory() : void
    {
        // assemble
        $instance = new FakeService(new stdClass());
        $services = new Services();
        $services->setServiceInstance(FakeService::class, $instance);
        $ioc = new AutowiredContainer($services);

        // act & assert
        $this->assertTrue($ioc->hasService(FakeService::class));
        $actual = $ioc->getService(FakeService::class);
        $this->assertSame($instance, $actual);
        $again = $ioc->getService(FakeService::class);
        $this->assertSame($actual, $again);
    }

    public function testGetService_factoryBeforeAutowired() : void
    {
        // assemble
        $services = new Services();

        $services->setServiceFactory(
            FakeService::class,
            fn (IocContainer $ioc) : FakeService => new FakeService(
                $ioc->getService(stdClass::class),
            ),
        );

        $ioc = new AutowiredContainer($services);

        // act & assert
        $this->assertTrue($ioc->hasService(FakeService::class));
        $actual = $ioc->getService(FakeService::class);
        $this->assertInstanceOf(FakeService::class, $actual);
        $this->assertInstanceOf(stdClass::class, $actual->dependency);
    }

    public function testGetService_broken() : void
    {
        // assemble
        $ioc = new AutowiredContainer();

        // act & assert
        $this->assertTrue($ioc->hasService(FakeServiceBroken::class));
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage("Cannot create argument for 'IocInterop\Impl\FakeServiceBroken::\$dependency' of type 'SplFileObject|stdClass'.");
        $ioc->getService(FakeServiceBroken::class);
    }
}
