<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use Attribute;
use IocInterop\Interface\IocParameterResolver;
use IocInterop\Interface\IocContainer;
use ReflectionParameter;

#[Attribute(Attribute::TARGET_PARAMETER)]
class NewService implements IocParameterResolver
{
    public function __construct(protected string $serviceName)
    {
    }

    public function resolveParameter(
        IocContainer $ioc,
        ReflectionParameter $parameter,
    ) : mixed
    {
        return $ioc->newService($this->serviceName);
    }
}
