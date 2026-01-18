<?php
declare(strict_types=1);

namespace IocInterop\Impl\Fake;

use IocInterop\Impl\Attribute\GetEnv;
use IocInterop\Impl\Attribute\GetService;
use IocInterop\Impl\Attribute\NewService;
use stdClass;

class FakeServiceWithAttributes
{
    // @phpstan-ignore missingType.parameter
    public function __construct(
        #[GetService('foo')] public stdClass $foo,
        #[NewService('bar')] public stdClass $bar,
        #[GetEnv('BAZ')] public string $baz,
        #[GetEnv('DIB')] public int $dib,
        #[GetEnv('GIR')] public $gir,
    ) {
    }
}
