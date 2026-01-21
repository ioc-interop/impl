<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocThrowable;
use RuntimeException;

class IocException extends RuntimeException implements IocThrowable
{
}
