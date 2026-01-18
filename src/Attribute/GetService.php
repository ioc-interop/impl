<?php
declare(strict_types=1);

namespace IocInterop\Impl\Attribute;

use Attribute;
use IocInterop\Interface\IocContainer;
use IocInterop\Interface\Resolver\IocParameterResolver;
use ReflectionParameter;

#[Attribute(Attribute::TARGET_PARAMETER)]
class GetService implements IocParameterResolver
{
    public function __construct(protected string $serviceName)
    {
    }

    public function resolveParameter(
        IocContainer $ioc,
        ReflectionParameter $parameter,
    ) : mixed
    {
        return $ioc->getService($this->serviceName);
    }
}
