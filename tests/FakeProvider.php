<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocProvider;
use IocInterop\Interface\IocServices;
use stdClass;

class FakeProvider implements IocProvider
{
    public function provideServices(IocServices $services) : void
    {
        $services->setServiceFactory(
            stdClass::class,
            fn (IocContainer $ioc) : stdClass => new stdClass(),
        );
    }
}
