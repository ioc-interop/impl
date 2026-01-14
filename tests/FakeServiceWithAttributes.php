<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use stdClass;

class FakeServiceWithAttributes
{
    public function __construct(
        #[GetService('foo')]
        public stdClass $foo,
        #[NewService('bar')]
        public stdClass $bar,
    ) {
    }
}
