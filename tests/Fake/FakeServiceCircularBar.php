<?php
declare(strict_types=1);

namespace IocInterop\Impl\Fake;

class FakeServiceCircularBar
{
    public function __construct(
        public FakeServiceCircularFoo $foo
    ) {
    }
}
