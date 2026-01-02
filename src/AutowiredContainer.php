<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocServices;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

class AutowiredContainer extends ProtectedContainer
{
    /**
     * @var array<class-string, ReflectionClass<object>>
     */
    protected array $reflection = [];

    public function __construct(
        protected IocServices $services = new Services()
    ) {
    }

    public function hasService(string $serviceName) : bool
    {
        $serviceName = $this->services->hasServiceAlias($serviceName)
            ? $this->services->getServiceAlias($serviceName)
            : $serviceName;

        return $this->services->hasServiceInstance($serviceName)
            || $this->services->hasServiceFactory($serviceName)
            || $this->isInstantiable($serviceName);
    }

    protected function isInstantiable(string $serviceName) : bool
    {
        return class_exists($serviceName)
            && $this->getReflection($serviceName)->isInstantiable();
    }

    /**
     * @param class-string $serviceName
     * @return ReflectionClass<object>
     */
    protected function getReflection(string $serviceName) : ReflectionClass
    {
        $this->reflection[$serviceName] ??= new ReflectionClass($serviceName);
        return $this->reflection[$serviceName];
    }

    /**
     * @param class-string $serviceName
     */
    protected function instantiate(string $serviceName) : object
    {
        $circular = $this->instantiating[$serviceName] ?? false;

        if ($circular) {
            throw new ContainerException("Circular dependency");
        }

        $this->instantiating[$serviceName] = true;

        if ($this->services->hasServiceFactory($serviceName)) {
            $serviceFactory = $this->services->getServiceFactory($serviceName);
            $instance = $serviceFactory($this);
        } else {
            $arguments = $this->arguments($serviceName);
            $instance = new $serviceName(...$arguments);
        }

        unset($this->instantiating[$serviceName]);
        return $instance;
    }

    /**
     * @param class-string $serviceName
     * @return mixed[]
     */
    protected function arguments(string $serviceName) : array
    {
        $arguments = [];
        $constructor = $this->getReflection($serviceName)->getConstructor();

        if (! $constructor) {
            return $arguments;
        }

        $parameters = $constructor->getParameters();

        foreach ($parameters as $parameter) {
            $parameterName = $parameter->getName();
            $arguments[$parameterName] = $this->argument($serviceName, $parameter);
        }

        return $arguments;
    }

    protected function argument(
        string $serviceName,
        ReflectionParameter $parameter,
    ) : mixed
    {
        $parameterType = $parameter->getType();

        if (! $parameterType instanceof ReflectionNamedType) {
            return $this->argumentDefault($serviceName, $parameter);
        }

        $parameterClass = $parameterType->getName();

        if ($this->hasService($parameterClass)) {
            return $this->getService($parameterClass);
        }

        return $this->argumentDefault($serviceName, $parameter);
    }

    protected function argumentDefault(
        string $serviceName,
        ReflectionParameter $parameter,
    ) : mixed
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $parameterName = $parameter->getName();
        $parameterType = $parameter->getType();

        $message = "Cannot create argument for "
            . "'{$serviceName}::\${$parameterName}' "
            . "of type '{$parameterType}'.";

        throw new ContainerException($message);
    }
}
