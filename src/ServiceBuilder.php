<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocServiceBuilder;
use IocInterop\Interface\IocTypeAliases;

/**
 * @phpstan-import-type ioc_service_extender_callable from IocTypeAliases
 * @phpstan-import-type ioc_service_factory_callable from IocTypeAliases
 * @phpstan-import-type ioc_service_name_string from IocTypeAliases
 */
class ServiceBuilder implements IocServiceBuilder
{
    /**
     * @var ?ioc_service_factory_callable
     */
    protected mixed $serviceFactory = null;

    /**
     * @var ioc_service_extender_callable[]
     */
    protected array $serviceExtenders = [];

    /**
     * @param ioc_service_name_string $serviceName
     */
    public function __construct(
        protected string $serviceName,
        protected ServiceResolver $serviceResolver,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function isServiceBuildable() : bool
    {
        return $this->hasServiceFactory()
            || $this->serviceResolver->isServiceResolvable($this->serviceName);
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
            ?? throw new ContainerException("Builder for '{$this->serviceName}' has no factory.");
    }

    /**
     * @inheritdoc
     */
    public function setServiceFactory(callable $serviceFactory) : self
    {
        $this->serviceFactory = $serviceFactory;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function unsetServiceFactory() : self
    {
        $this->serviceFactory = null;
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
    public function buildService(IocContainer $ioc) : object
    {
        if ($this->hasServiceFactory()) {
            $serviceFactory = $this->getServiceFactory();
            $service = $serviceFactory($ioc);
        } else {
            $service = $this->serviceResolver->resolveService(
                $ioc,
                $this->serviceName,
            );
        }

        foreach ($this->getServiceExtenders() as $serviceExtender) {
            $service = $serviceExtender($ioc, $service);
        }

        return $service;
    }
}
