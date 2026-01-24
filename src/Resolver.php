<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Impl\IocException;
use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocParameterResolver;
use IocInterop\Interface\IocParametersResolver;
use IocInterop\Interface\IocResolver;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

class Resolver implements IocResolver, IocParametersResolver, IocParameterResolver
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
    public function isResolvable(string $class) : bool
    {
        return class_exists($class)
            && $this->getReflection($class)->isInstantiable();
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        IocContainer $ioc,
        string $class,
        array $arguments = [],
    ) : object
    {
        if (! $this->isResolvable($class)) {
            throw new IocException(
                "Class '{$class}' is not resolvable."
            );
        }

        if (in_array($class, $this->resolving)) {
            $message = implode(", ", $this->resolving) . ", {$class}";
            throw new IocException("Circular dependency: $message");
        }

        $this->resolving[] = $class;

        $parameters = $this
            ->getReflection($class)
            ->getConstructor()
            ?->getParameters()
            ?? [];

        $arguments = $this->resolveParameters(
            $ioc,
            $parameters,
            $arguments,
        );

        $service = new $class(...$arguments);
        array_pop($this->resolving);
        return $service;
    }

    /**
     * @inheritdoc
     */
    public function resolveParameters(
        IocContainer $ioc,
        array $parameters,
        array $arguments = [],
    ) : array
    {
        foreach ($parameters as $parameter) {
            $parameterName = $parameter->getName();

            if (array_key_exists($parameterName, $arguments)) {
                continue;
            }

            $arguments[$parameterName] = $this->resolveParameter(
                $ioc,
                $parameter,
            );
        }

        return $arguments;
    }

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
}
