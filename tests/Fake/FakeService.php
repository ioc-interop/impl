<?php
declare(strict_types=1);

namespace IocInterop\Impl\Fake;

use stdClass;

class FakeService
{
    public function __construct(
        public ?stdClass $dependency = null,
        public string $nonNamedType = 'fake'
    ) {
    }
}
