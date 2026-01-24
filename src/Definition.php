<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use Closure;
use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocDefinition;
use IocInterop\Interface\IocResolver;
use IocInterop\Interface\IocTypeAliases;
use ReflectionFunction;
use ReflectionParameter;

/**
 * @phpstan-import-type ioc_service_extender_callable from IocTypeAliases
 * @phpstan-import-type ioc_service_factory_callable from IocTypeAliases
 * @phpstan-import-type ioc_service_name_string from IocTypeAliases
 */
class Definition implements IocDefinition
{
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
    public function hasFactory() : bool
    {
        return (bool) $this->serviceFactory;
    }

    /**
     * @inheritdoc
     */
    public function getFactory() : callable
    {
        return $this->serviceFactory
            ?? throw new IocException("Builder for '{$this->serviceName}' has no factory.");
    }

    /**
     * @inheritdoc
     */
    public function setFactory(callable $serviceFactory) : self
    {
        $this->serviceFactory = $serviceFactory;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function unsetFactory() : self
    {
        $this->serviceFactory = null;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addExtender(callable $serviceExtender) : self
    {
        $this->serviceExtenders[] = $serviceExtender;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasExtenders() : bool
    {
        return (bool) $this->serviceExtenders;
    }

    /**
     * @inheritdoc
     */
    public function getExtenders() : array
    {
        return $this->serviceExtenders;
    }

    /**
     * @inheritdoc
     */
    public function setExtenders(array $serviceExtenders) : self
    {
        $this->unsetExtenders();

        foreach ($serviceExtenders as $serviceExtender) {
            $this->addExtender($serviceExtender);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function unsetExtenders() : self
    {
        $this->serviceExtenders = [];
        return $this;
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
            $service = $ioc
                ->getService(IocResolver::class)
                ->resolve(
                    $ioc,
                    $this->serviceName,
                );
        }

        foreach ($this->getExtenders() as $serviceExtender) {
            $service = $serviceExtender($ioc, $service);
        }

        return $service;
    }
}
