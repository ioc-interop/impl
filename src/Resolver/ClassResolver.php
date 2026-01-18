<?php
declare(strict_types=1);

namespace IocInterop\Impl\Resolver;

use IocInterop\Impl\ContainerException;
use IocInterop\Interface\IocContainer;
use IocInterop\Interface\Resolver\IocClassResolver;
use IocInterop\Interface\Resolver\IocParameterResolver;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

class ClassResolver implements IocClassResolver
{
    /**
     * @var array<string, ReflectionClass<object>>
     */
    protected array $reflection = [];

    /**
     * @var list<string>
     */
    protected array $resolving = [];

    /**
     * @inheritdoc
     */
    public function isServiceResolvable(string $class) : bool
    {
        return class_exists($class)
            && $this->getReflection($class)->isInstantiable();
    }

    /**
     * @inheritdoc
     */
    public function resolveService(
        IocContainer $ioc,
        string $class,
        array $serviceArgs = [],
    ) : object
    {
        if (! $this->isServiceResolvable($class)) {
            throw new ContainerException(
                "Service '{$class}' is not resolvable."
            );
        }

        $this->resolving[] = $class;

        $parameters = $this
            ->getReflection($class)
            ->getConstructor()
            ?->getParameters()
            ?? [];

        foreach ($parameters as $parameter) {
            $parameterName = $parameter->getName();

            if (! array_key_exists($parameterName, $serviceArgs)) {
                $serviceArgs[$parameterName] = $this->resolveConstructorParameter(
                    $ioc,
                    $parameter,
                );
            }
        }

        $service = new $class(...$serviceArgs);
        array_pop($this->resolving);
        return $service;
    }

    /**
     * @param string $class
     * @return ReflectionClass<object>
     */
    protected function getReflection(string $class) : ReflectionClass
    {
        /** @var class-string $class */
        $this->reflection[$class] ??= new ReflectionClass($class);
        return $this->reflection[$class];
    }

    protected function resolveConstructorParameter(
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
            return $this->resolveConstructorParameterFromDefault(
                $parameter,
            );
        }

        $parameterClass = $parameterType->getName();

        if ($ioc->hasService($parameterClass)) {
            return $ioc->getService($parameterClass);
        }

        return $this->resolveConstructorParameterFromDefault($parameter);
    }

    protected function resolveConstructorParameterFromDefault(
        ReflectionParameter $parameter,
    ) : mixed
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        /** @var string $class */
        $class = end($this->resolving);
        $parameterName = $parameter->getName();
        $parameterType = $parameter->getType();

        $message = "Cannot create argument for '{$class}' constructor "
            . "parameter name '\${$parameterName}' "
            . "of type '{$parameterType}'.";

        throw new ContainerException($message);
    }
}
