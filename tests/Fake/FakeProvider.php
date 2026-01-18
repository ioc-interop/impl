<?php
declare(strict_types=1);

namespace IocInterop\Impl\Fake;

use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocServicesProvider;
use IocInterop\Interface\IocServices;
use stdClass;

class FakeProvider implements IocServicesProvider
{
    public function provideServices(IocServices $services) : void
    {
        $services
            ->getServiceBuilder(stdClass::class)
            ->setServiceFactory(
                fn (IocContainer $ioc) : stdClass => new stdClass(),
            );
    }
}
