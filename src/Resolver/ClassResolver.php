<?php
declare(strict_types=1);

namespace IocInterop\Impl\Resolver;

use IocInterop\Impl\IocException;
use IocInterop\Interface\IocContainer;
use IocInterop\Interface\Resolver\IocClassResolver;
use IocInterop\Interface\Resolver\IocParametersResolver;
use ReflectionClass;

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
    public function isClassResolvable(string $class) : bool
    {
        return class_exists($class)
            && $this->getReflection($class)->isInstantiable();
    }

    /**
     * @inheritdoc
     */
    public function resolveClass(
        IocContainer $ioc,
        string $class,
        array $arguments = [],
    ) : object
    {
        if (! $this->isClassResolvable($class)) {
            throw new IocException(
                "Service '{$class}' is not resolvable."
            );
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
