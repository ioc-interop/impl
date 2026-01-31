<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocThrowable;
use RuntimeException;

class ContainerException extends RuntimeException implements IocThrowable
{
}
