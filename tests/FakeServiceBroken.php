<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use SplFileObject;
use stdClass;

class FakeServiceBroken implements FakeServiceInterface
{
    public function __construct(
        public SplFileObject|stdClass $dependency
    ) {
    }
}
