<?php
declare(strict_types=1);

namespace IocInterop\Impl\Fake;

class FakeServiceCircularFoo
{
    public function __construct(
        public FakeServiceCircularBar $bar
    ) {
    }
}
