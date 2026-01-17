<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use Attribute;
use EnvInterop\Interface\EnvGetter;
use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocParameterResolver;
use ReflectionParameter;

#[Attribute(Attribute::TARGET_PARAMETER)]
class GetEnv implements IocParameterResolver
{
    public function __construct(protected string $name)
    {
    }

    /**
     * Returns an environment value; attempts to cast the value to the parameter
     * type.
     */
    public function resolveParameter(
        IocContainer $ioc,
        ReflectionParameter $parameter,
    ) : mixed
    {
        $value = $ioc->getService(EnvGetter::class)->getEnv($this->name);
        $type = (string) $parameter->getType();

        if ($type) {
            settype($value, $type);
        }

        return $value;
    }
}
