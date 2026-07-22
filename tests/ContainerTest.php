<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Impl\FakeService;
use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocThrowable;
use stdClass;

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    public function newContainer() : IocContainer
    {
        $containerFactory = new ContainerFactory();

        $containerFactory->instances = [stdClass::class => new stdClass()];

        $containerFactory->factories = [
            FakeService::class => fn (IocContainer $ioc)
                => new FakeService(dependency: $ioc->getService(stdClass::class)),
            'foo' => fn (IocContainer $ioc) => (object) ['bar' => 'baz'],
        ];

        return $containerFactory->newContainer();
    }

    public function testHasService() : void
    {
        $ioc = $this->newContainer();
        $this->assertTrue($ioc->hasService(stdClass::class));
        $this->assertTrue($ioc->hasService(FakeService::class));
        $this->assertTrue($ioc->hasService('foo'));
        $this->assertFalse($ioc->hasService('noSuchService'));
    }

    public function testGetService() : void
    {
        $ioc = $this->newContainer();
        $stdClass = $ioc->getService(stdClass::class);
        $fakeService = $ioc->getService(FakeService::class);
        $this->assertSame($stdClass, $fakeService->dependency);

        /** @var object{bar: string} $foo */
        $foo = $ioc->getService('foo');
        $this->assertSame('baz', $foo->bar);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Service not available: noSuchService');
        $ioc->getService('noSuchService');
    }

    public function testFactoryResultIsSharedAcrossCalls() : void
    {
        $ioc = $this->newContainer();

        $this->assertSame(
            $ioc->getService(FakeService::class),
            $ioc->getService(FakeService::class),
        );
    }

    public function testMissThrowsIocThrowable() : void
    {
        $ioc = $this->newContainer();

        $this->expectException(IocThrowable::class);
        $ioc->getService('noSuchService');
    }
}
