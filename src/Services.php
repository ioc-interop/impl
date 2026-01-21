<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Impl\Resolver\ClassResolver;
use IocInterop\Interface\IocServiceBuilder;
use IocInterop\Interface\IocServices;
use IocInterop\Interface\IocTypeAliases;
use IocInterop\Interface\Resolver\IocClassResolver;

/**
 * @phpstan-import-type ioc_service_name_string from IocTypeAliases
 */
class Services implements IocServices
{
    /**
     * @var array<ioc_service_name_string, object>
     */
    protected array $instances = [];

    /**
     * @var array<ioc_service_name_string, IocServiceBuilder>
     */
    protected array $builders = [];

    /**
     * @var array<ioc_service_name_string, ioc_service_name_string>
     */
    protected array $aliases = [];

    public function __construct(
        IocClassResolver $classResolver = new ClassResolver(),
    ) {
        $this->setServiceInstance(IocClassResolver::class, $classResolver);
    }

    /**
     * @inheritdoc
     */
    public function hasServiceInstance(string $serviceName) : bool
    {
        return isset($this->instances[$serviceName]);
    }

    /**
     * @inheritdoc
     */
    public function getServiceInstance(string $serviceName) : object
    {
        $instance = $this->instances[$serviceName]
            ?? throw new IocException("No shared instance for '{$serviceName}'.");

        return $instance;
    }

    /**
     * @inheritdoc
     */
    public function setServiceInstance(string $serviceName, object $instance) : void
    {
        $this->instances[$serviceName] = $instance;
    }

    /**
     * @inheritdoc
     */
    public function unsetServiceInstance(string $serviceName) : void
    {
        unset($this->instances[$serviceName]);
    }

    /**
     * @inheritdoc
     */
    public function hasServiceBuilder(string $serviceName) : bool
    {
        return isset($this->builders[$serviceName]);
    }

    /**
     * @inheritdoc
     */
    public function getServiceBuilder(string $serviceName) : IocServiceBuilder
    {
        $this->builders[$serviceName] ??= $this->newServiceBuilder($serviceName);
        return $this->builders[$serviceName];
    }

    /**
     * @inheritdoc
     */
    public function setServiceBuilder(string $serviceName, IocServiceBuilder $serviceBuilder) : void
    {
        $this->builders[$serviceName] = $serviceBuilder;
    }

    /**
     * @inheritdoc
     */
    public function unsetServiceBuilder(string $serviceName) : void
    {
        unset($this->builders[$serviceName]);
    }

    /**
     * @inheritdoc
     */
    public function newServiceBuilder(string $serviceName) : IocServiceBuilder
    {
        return new ServiceBuilder($serviceName);
    }

    /**
     * @inheritdoc
     */
    public function hasServiceAlias(string $serviceName) : bool
    {
        return isset($this->aliases[$serviceName]);
    }

    /**
     * @inheritdoc
     */
    public function getServiceAlias(string $serviceName) : string
    {
        if (! isset($this->aliases[$serviceName])) {
            throw new IocException("No alias for '{$serviceName}'.");
        }

        while (isset($this->aliases[$serviceName])) {
            $serviceName = $this->aliases[$serviceName];
        }

        return $serviceName;
    }

    /**
     * @inheritdoc
     */
    public function setServiceAlias(string $serviceName, string $serviceAlias) : void
    {
        $circular = [];

        while (isset($this->aliases[$serviceAlias])) {
            if (isset($circular[$serviceAlias])) {
                throw new IocException("Circular alias");
            }

            $serviceAlias = $this->aliases[$serviceAlias];
            $circular[$serviceAlias] = true;
        }

        $this->aliases[$serviceName] = $serviceAlias;
    }

    /**
     * @inheritdoc
     */
    public function unsetServiceAlias(string $serviceName) : void
    {
        unset($this->aliases[$serviceName]);
    }
}
