<?php
declare(strict_types=1);

namespace IocInterop\Impl\Fake;

use SplFileObject;
use stdClass;

class FakeServiceUnionType implements FakeServiceInterface
{
    public function __construct(
        public SplFileObject|stdClass $dependency
    ) {
    }
}
