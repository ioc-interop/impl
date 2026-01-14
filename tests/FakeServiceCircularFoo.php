<?php
declare(strict_types=1);

namespace IocInterop\Impl;

class FakeServiceCircularFoo
{
    public function __construct(
        public FakeServiceCircularBar $bar
    ) {
    }
}
