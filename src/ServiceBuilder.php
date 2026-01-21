<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use Closure;
use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocServiceBuilder;
use IocInterop\Interface\Resolver\IocClassResolver;
use IocInterop\Interface\IocTypeAliases;
use ReflectionFunction;
use ReflectionParameter;

/**
 * @phpstan-import-type ioc_service_extender_callable from IocTypeAliases
 * @phpstan-import-type ioc_service_factory_callable from IocTypeAliases
 * @phpstan-import-type ioc_service_name_string from IocTypeAliases
 */
class ServiceBuilder implements IocServiceBuilder
{
    /**
     * @var array<int, ?string>
     */
    protected array $factoryParameterTypes = [];

    /**
     * @var ?ioc_service_factory_callable
     */
    protected mixed $serviceFactory = null;

    /**
     * @var ioc_service_extender_callable[]
     */
    protected array $serviceExtenders = [];

    public function __construct(protected string $serviceName)
    {
    }

    /**
     * @inheritdoc
     */
    public function hasServiceFactory() : bool
    {
        return (bool) $this->serviceFactory;
    }

    /**
     * @inheritdoc
     */
    public function getServiceFactory() : callable
    {
        return $this->serviceFactory
            ?? throw new IocException("Builder for '{$this->serviceName}' has no factory.");
    }

    /**
     * @inheritdoc
     */
    public function setServiceFactory(callable $serviceFactory) : self
    {
        $this->serviceFactory = $serviceFactory;

        $this->factoryParameterTypes = [
            0 => null,
            1 => null,
        ];

        $closure = $this->serviceFactory instanceof Closure
            ? $this->serviceFactory
            : Closure::fromCallable($this->serviceFactory);

        $parameters = new ReflectionFunction($closure)->getParameters();

        foreach ($parameters as $i => $parameter) {
            $this->factoryParameterTypes[$i] = (string) $parameter->getType();
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function runServiceFactory(
        IocContainer $ioc,
        array $arguments = []
    ) : object
    {
        $serviceFactory = $this->getServiceFactory();

        if (! $arguments) {
            return $serviceFactory($ioc);
        }

        $expect = 'array';
        $actual = $this->factoryParameterTypes[1];

        if ($expect !== $actual) {
            throw new IocException(
                "Expected {$expect} as second parameter type, got {$actual} instead."
            );
        }

        return $serviceFactory($ioc, $arguments);
    }

    /**
     * @inheritdoc
     */
    public function unsetServiceFactory() : self
    {
        $this->serviceFactory = null;
        $this->factoryParameterTypes = [];
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addServiceExtender(callable $serviceExtender) : self
    {
        $this->serviceExtenders[] = $serviceExtender;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasServiceExtenders() : bool
    {
        return (bool) $this->serviceExtenders;
    }

    /**
     * @inheritdoc
     */
    public function getServiceExtenders() : array
    {
        return $this->serviceExtenders;
    }

    /**
     * @inheritdoc
     */
    public function setServiceExtenders(array $serviceExtenders) : self
    {
        $this->unsetServiceExtenders();

        foreach ($serviceExtenders as $serviceExtender) {
            $this->addServiceExtender($serviceExtender);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function unsetServiceExtenders() : self
    {
        $this->serviceExtenders = [];
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function buildService(
        IocContainer $ioc,
        array $arguments = [],
    ) : object
    {
        $service = $this->hasServiceFactory()
            ? $this->runServiceFactory($ioc, $arguments)
            : $ioc
                ->getService(IocClassResolver::class)
                ->resolveClass(
                    $ioc,
                    $this->serviceName,
                    $arguments,
                );

        foreach ($this->getServiceExtenders() as $serviceExtender) {
            $service = $serviceExtender($ioc, $service);
        }

        return $service;
    }
}
