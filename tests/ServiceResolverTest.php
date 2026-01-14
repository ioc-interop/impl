<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;
use stdClass;
use DateTimeInterface;

class ServiceResolverTest extends \PHPUnit\Framework\TestCase
{
    public function testAutowired() : void
    {
        // assemble
        $ioc = new PublicContainer();
        $serviceResolver = new ServiceResolver();

        // act & assert
        $service = $serviceResolver->resolveService($ioc, FakeService::class);
        $this->assertInstanceOf(FakeService::class, $service);
    }

    public function testAttributed() : void
    {
        // assemble
        $ioc = new PublicContainer();

        $ioc->getServiceBuilder('foo')
            ->setServiceFactory(fn ($ioc) => (object) ['value' => 'foo']);

        $ioc->getServiceBuilder('bar')
            ->setServiceFactory(fn ($ioc) => (object) ['value' => 'bar']);

        $serviceResolver = new ServiceResolver();
        $service = $serviceResolver->resolveService($ioc, FakeServiceWithAttributes::class);
        $this->assertSame($service->foo->value, 'foo');
        $this->assertSame($service->bar->value, 'bar');

        $again = $serviceResolver->resolveService($ioc, FakeServiceWithAttributes::class);
        $this->assertSame($service->foo, $again->foo);
        $this->assertNotSame($service->bar, $again->bar);
    }

    public function testNotResolvable() : void
    {
        // assemble
        $ioc = new PublicContainer();
        $serviceResolver = new ServiceResolver();

        // act & assert
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage("Service 'IocInterop\Impl\FakeServiceInterface' is not resolvable.");
        $serviceResolver->resolveService($ioc, FakeServiceInterface::class);
    }

    public function testBroken() : void
    {
        // assemble
        $ioc = new PublicContainer();
        $serviceResolver = new ServiceResolver();

        // act & assert
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage("Cannot create argument for 'IocInterop\Impl\FakeServiceUnionType' constructor parameter name '\$dependency' of type 'SplFileObject|stdClass'.");
        $serviceResolver->resolveService($ioc, FakeServiceUnionType::class);
    }
}
