<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocDefinition;
use IocInterop\Interface\IocResolver;
use IocInterop\Interface\IocServices;
use IocInterop\Interface\IocTypeAliases;

/**
 * @phpstan-import-type ioc_service_lifetime_string from IocTypeAliases
 * @phpstan-import-type ioc_service_name_string from IocTypeAliases
 */
class Container implements IocContainer, IocServices
{
    /**
     * @var array<ioc_service_name_string, ioc_service_name_string>
     */
    protected array $aliases = [];

    /**
     * @var array<ioc_service_name_string, IocDefinition>
     */
    protected array $definitions = [];

    /**
     * @var array<ioc_service_lifetime_string, array<ioc_service_name_string, object>>
     */
    protected array $instances = [
        IocServices::SCOPED => [],
        IocServices::SINGLETON => [],
    ];

    public function __construct(
        protected IocResolver $resolver = new Resolver()
    ) {
        $this->setInstance(IocResolver::class, $resolver, IocServices::SINGLETON);
        $this->setInstance(IocContainer::class, $this, IocServices::SINGLETON);
    }

    /**
     * @inheritdoc
     */
    public function getService(string $serviceName) : object
    {
        $serviceName = $this->hasAlias($serviceName)
            ? $this->getAlias($serviceName)
            : $serviceName;

        if ($this->hasInstance($serviceName)) {
            return $this->getInstance($serviceName);
        }

        $definition = $this->getDefinition($serviceName);
        $instance = $definition->buildInstance($this);
        $lifetime = $definition->getLifetime();

        if ($lifetime !== IocServices::TRANSIENT) {
            $this->setInstance(
                $serviceName,
                $instance,
                $lifetime,
            );
        }

        return $instance;
    }

    /**
     * @inheritdoc
     */
    public function hasService(string $serviceName) : bool
    {
        $serviceName = $this->hasAlias($serviceName)
            ? $this->getAlias($serviceName)
            : $serviceName;

        if ($this->hasInstance($serviceName)) {
            return true;
        }

        $hasDefinedFactory = $this->hasDefinition($serviceName)
            && $this->getDefinition($serviceName)->hasFactory();

        if ($hasDefinedFactory) {
            return true;
        }

        return $this->getService(IocResolver::class)
            ->isResolvable($serviceName);
    }

    /**
     * @inheritdoc
     */
    public function hasInstance(string $serviceName) : bool
    {
        return isset($this->instances[IocServices::SCOPED][$serviceName])
            || isset($this->instances[IocServices::SINGLETON][$serviceName]);
    }

    /**
     * @inheritdoc
     */
    public function getInstance(string $serviceName) : object
    {
        return $this->instances[IocServices::SCOPED][$serviceName]
            ?? $this->instances[IocServices::SINGLETON][$serviceName]
            ?? throw new IocException("No shared instance for '{$serviceName}'.");
    }

    /**
     * @inheritdoc
     */
    public function setInstance(
        string $serviceName,
        object $instance,
        string $lifetime = IocServices::SCOPED,
    ) : void
    {
        if ($lifetime === IocServices::TRANSIENT) {
            throw new IocException("Cannot set a transient service.");
        }

        $otherLifetime = $lifetime === IocServices::SCOPED
            ? IocServices::SINGLETON
            : IocServices::SCOPED;

        $this->instances[$lifetime][$serviceName] = $instance;
        unset($this->instances[$otherLifetime][$serviceName]);
    }

    /**
     * @inheritdoc
     */
    public function unsetInstance(string $serviceName) : void
    {
        unset($this->instances[IocServices::SCOPED][$serviceName]);
        unset($this->instances[IocServices::SINGLETON][$serviceName]);
    }

    /**
     * @inheritdoc
     */
    public function unsetInstances(string $lifetime) : void
    {
        $this->instances[$lifetime] = [];
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
