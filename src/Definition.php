<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocDefinition;
use IocInterop\Interface\IocResolver;
use IocInterop\Interface\IocServices;
use IocInterop\Interface\IocTypeAliases;

/**
 * @phpstan-import-type ioc_service_extender_callable from IocTypeAliases
 * @phpstan-import-type ioc_service_factory_callable from IocTypeAliases
 * @phpstan-import-type ioc_service_name_string from IocTypeAliases
 * @phpstan-import-type ioc_service_lifetime_string from IocTypeAliases
 */
class Definition implements IocDefinition
{
    /**
     * @var ?ioc_service_factory_callable
     */
    protected mixed $factory = null;

    /**
     * @var ioc_service_extender_callable[]
     */
    protected array $extenders = [];

    /**
     * @var ioc_service_lifetime_string
     */
    protected string $lifetime = IocServices::SCOPED;

    public function __construct(protected string $serviceName)
    {
    }

    /**
     * @inheritdoc
     */
    public function hasFactory() : bool
    {
        return (bool) $this->factory;
    }

    /**
     * @inheritdoc
     */
    public function getFactory() : callable
    {
        return $this->factory
            ?? throw new IocException("Builder for '{$this->serviceName}' has no factory.");
    }

    /**
     * @inheritdoc
     */
    public function setFactory(callable $factory) : self
    {
        $this->factory = $factory;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function unsetFactory() : self
    {
        $this->factory = null;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addExtender(callable $extender) : self
    {
        $this->extenders[] = $extender;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasExtenders() : bool
    {
        return (bool) $this->extenders;
    }

    /**
     * @inheritdoc
     */
    public function getExtenders() : array
    {
        return $this->extenders;
    }

    /**
     * @inheritdoc
     */
    public function setExtenders(array $extenders) : self
    {
        $this->unsetExtenders();

        foreach ($extenders as $extender) {
            $this->addExtender($extender);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function unsetExtenders() : self
    {
        $this->extenders = [];
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setLifetime(string $lifetime) : self
    {
        $this->lifetime = $lifetime;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLifetime() : string
    {
        return $this->lifetime ?? IocServices::SCOPED;
    }

    /**
     * @inheritdoc
     */
    public function buildInstance(IocContainer $ioc) : object
    {
        if ($this->hasFactory()) {
            $factory = $this->getFactory();
            $service = $factory($ioc);
        } else {
            $service = $ioc->getService(IocResolver::class)
                ->resolve(
                    $ioc,
                    $this->serviceName,
                );
        }

        foreach ($this->getExtenders() as $extender) {
            $service = $extender($ioc, $service);
        }

        return $service;
    }
}
