<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Impl\Fake\FakeContainer;
use IocInterop\Impl\Resolver;
use IocInterop\Interface\IocContainer;
use stdClass;

class InstanceFactoryTest extends \PHPUnit\Framework\TestCase
{
    protected Resolver $resolver;

    public function test() : void
    {
        $ioc = new Container();
        $instanceFactory = $ioc->getService(InstanceFactory::class);
        $actual = $instanceFactory->newInstance(stdClass::class);
        $this->assertInstanceOf(stdClass::class, $actual);
    }
}
