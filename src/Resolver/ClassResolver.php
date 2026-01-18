<?php
declare(strict_types=1);

namespace IocInterop\Impl\Resolver;

use IocInterop\Impl\ContainerException;
use IocInterop\Interface\IocContainer;
use IocInterop\Interface\Resolver\IocClassResolver;
use IocInterop\Interface\Resolver\IocParametersResolver;
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

    public function __construct(
        protected IocParametersResolver $parametersResolver = new ParametersResolver(),
    ) {
    }

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

        $serviceArgs = $this->parametersResolver->resolveParameters(
            $ioc,
            $parameters,
            $serviceArgs,
        );

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
}
