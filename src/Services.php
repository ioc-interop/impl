<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocServices;

class Services implements IocServices
{
    /**
     * @var array<string, object>
     */
    protected array $instances = [];

    /**
     * @var array<string, callable>
     */
    protected array $factories = [];

    /**
     * @var array<string, string>
     */
    protected array $aliases = [];

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
            ?? throw new ContainerException("No shared instance for '{$serviceName}'.");

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
    public function hasServiceFactory(string $serviceName) : bool
    {
        return isset($this->factories[$serviceName]);
    }

    /**
     * @inheritdoc
     */
    public function getServiceFactory(string $serviceName) : callable
    {
        return $this->factories[$serviceName]
            ?? throw new ContainerException("No factory for '{$serviceName}'.");
    }

    /**
     * @inheritdoc
     */
    public function setServiceFactory(string $serviceName, callable $serviceFactory) : void
    {
        $this->factories[$serviceName] = $serviceFactory;
    }

    /**
     * @inheritdoc
     */
    public function unsetServiceFactory(string $serviceName) : void
    {
        unset($this->factories[$serviceName]);
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
        return $this->aliases[$serviceName]
            ?? throw new ContainerException("No alias for '{$serviceName}'.");
    }

    /**
     * @inheritdoc
     */
    public function setServiceAlias(string $serviceName, string $serviceAlias) : void
    {
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
