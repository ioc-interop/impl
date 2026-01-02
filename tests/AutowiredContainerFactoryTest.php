<?php
declare(strict_types=1);

namespace IocInterop\Impl;

class AutowiredContainerFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function test() : void
    {
        $iocContainerFactory = new AutowiredContainerFactory(
            provider: new FakeProvider(),
        );

        $ioc = $iocContainerFactory->newContainer();

        $this->assertInstanceOf(
            FakeService::class,
            $ioc->getService(FakeService::class),
        );
    }
}
