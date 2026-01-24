<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Impl\IocException;
use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocResolver;
use IocInterop\Interface\IocParametersResolver;
use ReflectionClass;

class Resolver implements IocResolver
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
                "Service '{$class}' is not resolvable."
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

        $arguments = $this->parametersResolver->resolveParameters(
            $ioc,
            $parameters,
            $arguments,
        );

        $service = new $class(...$arguments);
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
