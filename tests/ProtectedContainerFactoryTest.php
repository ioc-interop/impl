<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use stdClass;

class ProtectedContainerFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function test() : void
    {
        $iocContainerFactory = new ProtectedContainerFactory(
            provider: new FakeProvider(),
        );

        $ioc = $iocContainerFactory->newContainer();

        $this->assertInstanceOf(
            stdClass::class,
            $ioc->getService(stdClass::class),
        );
    }
}
