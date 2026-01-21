<?php
declare(strict_types=1);

namespace IocInterop\Impl\Resolver;

use IocInterop\Impl\IocException;
use IocInterop\Interface\IocContainer;
use IocInterop\Interface\Resolver\IocParameterResolver;
use ReflectionAttribute;
use ReflectionNamedType;
use ReflectionParameter;

class ParameterResolver implements IocParameterResolver
{
    public function resolveParameter(
        IocContainer $ioc,
        ReflectionParameter $parameter,
    ) : mixed
    {
        /** @var ReflectionAttribute<object>[] */
        $attributes = $parameter->getAttributes();

        foreach ($attributes as $attribute) {
            if (is_a($attribute->name, IocParameterResolver::class, true)) {
                /** @var IocParameterResolver $parameterResolver */
                $parameterResolver = $attribute->newInstance();
                return $parameterResolver->resolveParameter($ioc, $parameter);
            }
        }

        $parameterType = $parameter->getType();

        $serviceName = $parameterType instanceof ReflectionNamedType
            ? $parameterType->getName()
            : null;

        if ($serviceName && $ioc->hasService($serviceName)) {
            return $ioc->getService($serviceName);
        }

        return $parameter->isDefaultValueAvailable()
            ? $parameter->getDefaultValue()
            : $this->cannotResolveParameter($parameter);
    }

    protected function cannotResolveParameter(
        ReflectionParameter $parameter
    ) : never
    {
        $message = "Cannot resolve parameter for ";
        $class = $parameter->getDeclaringClass()?->name;

        if ($class) {
            $message .= "{$class}::";
        }

        $message .= $parameter->getDeclaringFunction()->name
            . '('
            . $parameter->getType()
            . ' $'
            . $parameter->getName()
            . ')';

        throw new IocException($message);
    }
}
