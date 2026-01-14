<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocParameterResolver;
use IocInterop\Interface\IocServiceResolver;
use IocInterop\Interface\IocTypeAliases;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * @phpstan-import-type ioc_service_name_string from IocTypeAliases
 */
class ServiceResolver implements IocServiceResolver
{
    /**
     * @var array<class-string, ReflectionClass<object>>
     */
    protected array $reflection = [];

    /**
     * @var list<ioc_service_name_string>
     */
    protected array $resolving = [];

    /**
     * @param ioc_service_name_string $serviceName
     */
    public function isServiceResolvable(string $serviceName) : bool
    {
        return class_exists($serviceName)
            && $this->getReflection($serviceName)->isInstantiable();
    }

    /**
     * @inheritdoc
     */
    public function resolveService(
        IocContainer $ioc,
        string $serviceName,
        array $serviceArgs = [],
    ) : object
    {
        if (! $this->isServiceResolvable($serviceName)) {
            throw new ContainerException(
                "Service '{$serviceName}' is not resolvable."
            );
        }

        $this->resolving[] = $serviceName;

        $parameters = $this
            ->getReflection($serviceName)
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

        $service = new $serviceName(...$serviceArgs);
        array_pop($this->resolving);
        return $service;
    }

    /**
     * @param ioc_service_name_string $serviceName
     * @return ReflectionClass<object>
     */
    protected function getReflection(string $serviceName) : ReflectionClass
    {
        /** @var class-string $serviceName */
        $this->reflection[$serviceName] ??= new ReflectionClass($serviceName);
        return $this->reflection[$serviceName];
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

        /** @var string $serviceName */
        $serviceName = end($this->resolving);
        $parameterName = $parameter->getName();
        $parameterType = $parameter->getType();

        $message = "Cannot create argument for '{$serviceName}' constructor "
            . "parameter name '\${$parameterName}' "
            . "of type '{$parameterType}'.";

        throw new ContainerException($message);
    }
}
