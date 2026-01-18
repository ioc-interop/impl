<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Impl\Fake\FakeProvider;
use stdClass;

class PublicContainerFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function test() : void
    {
        $iocContainerFactory = new PublicContainerFactory(
            provider: new FakeProvider(),
        );

        $ioc = $iocContainerFactory->newContainer();

        $this->assertInstanceOf(
            stdClass::class,
            $ioc->getService(stdClass::class),
        );
    }
}
