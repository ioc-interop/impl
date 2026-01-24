<?php
declare(strict_types=1);

namespace IocInterop\Impl\Fake;

use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocProvider;
use IocInterop\Interface\IocServices;
use stdClass;

class FakeProvider implements IocProvider
{
    public function provide(IocServices $services) : void
    {
        $services
            ->getDefinition(stdClass::class)
            ->setFactory(
                fn (IocContainer $ioc) : stdClass => new stdClass(),
            );
    }
}
