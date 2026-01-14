<?php
declare(strict_types=1);

namespace IocInterop\Impl;

class FakeServiceCircularBar
{
    public function __construct(
        public FakeServiceCircularFoo $foo
    ) {
    }
}
