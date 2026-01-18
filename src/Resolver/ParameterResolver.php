<?php
declare(strict_types=1);

namespace IocInterop\Impl\Resolver;

use IocInterop\Impl\ContainerException;
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

        if (! $parameterType instanceof ReflectionNamedType) {
            return $this->resolveParameterDefault(
                $parameter,
            );
        }

        $parameterClass = $parameterType->getName();

        if ($ioc->hasService($parameterClass)) {
            return $ioc->getService($parameterClass);
        }

        return $this->resolveParameterDefault($parameter);
    }

    protected function resolveParameterDefault(
        ReflectionParameter $parameter,
    ) : mixed
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

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

        throw new ContainerException($message);
    }
}
