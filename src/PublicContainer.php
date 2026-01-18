<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;
use IocInterop\Interface\Resolver\IocClassResolver;
use IocInterop\Impl\Resolver\ClassResolver;

/**
 * Typical container that allows in-flight resetting of services.
 */
class PublicContainer extends Services implements IocContainer
{
    /**
     * @var array<string,bool>
     */
    protected array $building = [];

    public function __construct(
        protected IocClassResolver $classResolver = new ClassResolver(),
    ) {
        $this->setServiceInstance(IocContainer::class, $this);
        $this->setServiceInstance(IocClassResolver::class, $classResolver);
    }

    /**
     * @inheritdoc
     */
    public function getService(string $serviceName) : object
    {
        $serviceName = $this->hasServiceAlias($serviceName)
            ? $this->getServiceAlias($serviceName)
            : $serviceName;

        $this->instances[$serviceName] ??= $this->newService($serviceName);
        return $this->instances[$serviceName];
    }

    /**
     * @inheritdoc
     */
    public function hasService(string $serviceName) : bool
    {
        $serviceName = $this->hasServiceAlias($serviceName)
            ? $this->getServiceAlias($serviceName)
            : $serviceName;

        if ($this->hasServiceInstance($serviceName)) {
            return true;
        }

        $hasBuilderAndFactory = $this->hasServiceBuilder($serviceName)
            && $this->getServiceBuilder($serviceName)->hasServiceFactory();

        if ($hasBuilderAndFactory) {
            return true;
        }

        return $this
            ->getService(IocClassResolver::class)
            ->isServiceResolvable($serviceName);
    }

    /**
     * @inheritdoc
     */
    public function newService(
        string $serviceName,
        array $serviceArgs = [],
    ) : object
    {
        $serviceName = $this->hasServiceAlias($serviceName)
            ? $this->getServiceAlias($serviceName)
            : $serviceName;

        $circular = $this->building[$serviceName] ?? false;

        if ($circular) {
            $message = implode(", ", array_keys($this->building)) . ", $serviceName";
            throw new ContainerException("Circular dependency: $message");
        }

        $this->building[$serviceName] = true;

        $service = $this->hasServiceBuilder($serviceName)
            ? $this
                ->getServiceBuilder($serviceName)
                ->buildService($this, $serviceArgs)
            : $this
                ->getService(IocClassResolver::class)
                ->resolveService($this, $serviceName, $serviceArgs);

        unset($this->building[$serviceName]);
        return $service;
    }
}
