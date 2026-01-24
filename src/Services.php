<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Impl\Resolver;
use IocInterop\Interface\IocDefinition;
use IocInterop\Interface\IocServices;
use IocInterop\Interface\IocTypeAliases;
use IocInterop\Interface\IocResolver;

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
     * @var array<ioc_service_name_string, IocDefinition>
     */
    protected array $definitions = [];

    /**
     * @var array<ioc_service_name_string, ioc_service_name_string>
     */
    protected array $aliases = [];

    public function __construct(IocResolver $resolver = new Resolver())
    {
        $this->setInstance(IocResolver::class, $resolver);
    }

    /**
     * @inheritdoc
     */
    public function hasInstance(string $serviceName) : bool
    {
        return isset($this->instances[$serviceName]);
    }

    /**
     * @inheritdoc
     */
    public function getInstance(string $serviceName) : object
    {
        $instance = $this->instances[$serviceName]
            ?? throw new IocException("No shared instance for '{$serviceName}'.");

        return $instance;
    }

    /**
     * @inheritdoc
     */
    public function setInstance(string $serviceName, object $instance) : void
    {
        $this->instances[$serviceName] = $instance;
    }

    /**
     * @inheritdoc
     */
    public function unsetInstance(string $serviceName) : void
    {
        unset($this->instances[$serviceName]);
    }

    /**
     * @inheritdoc
     */
    public function hasDefinition(string $serviceName) : bool
    {
        return isset($this->definitions[$serviceName]);
    }

    /**
     * @inheritdoc
     */
    public function getDefinition(string $serviceName) : IocDefinition
    {
        $this->definitions[$serviceName] ??= $this->newDefinition($serviceName);
        return $this->definitions[$serviceName];
    }

    /**
     * @inheritdoc
     */
    public function setDefinition(string $serviceName, IocDefinition $definition) : void
    {
        $this->definitions[$serviceName] = $definition;
    }

    /**
     * @inheritdoc
     */
    public function unsetDefinition(string $serviceName) : void
    {
        unset($this->definitions[$serviceName]);
    }

    /**
     * @inheritdoc
     */
    public function newDefinition(string $serviceName) : IocDefinition
    {
        return new Definition($serviceName);
    }

    /**
     * @inheritdoc
     */
    public function hasAlias(string $serviceName) : bool
    {
        return isset($this->aliases[$serviceName]);
    }

    /**
     * @inheritdoc
     */
    public function getAlias(string $serviceName) : string
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
    public function setAlias(string $serviceName, string $alias) : void
    {
        $circular = [];

        while (isset($this->aliases[$alias])) {
            if (isset($circular[$alias])) {
                throw new IocException("Circular alias");
            }

            $alias = $this->aliases[$alias];
            $circular[$alias] = true;
        }

        $this->aliases[$serviceName] = $alias;
    }

    /**
     * @inheritdoc
     */
    public function unsetAlias(string $serviceName) : void
    {
        unset($this->aliases[$serviceName]);
    }
}
