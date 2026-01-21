<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocServices;
use IocInterop\Interface\Resolver\IocClassResolver;
use IocInterop\Impl\Resolver\ClassResolver;

/**
 * A container of predefined services that cannot be reset in-flight.
 */
class ProtectedContainer implements IocContainer
{
    /**
     * @var array<string,bool>
     */
    protected array $building = [];

    public function __construct(
        protected IocServices $services = new Services(),
        protected IocClassResolver $classResolver = new ClassResolver(),
    ) {
        $this->services->setServiceInstance(IocContainer::class, $this);
        $this->services->setServiceInstance(IocClassResolver::class, $classResolver);
    }

    /**
     * @inheritdoc
     */
    public function getService(string $serviceName) : object
    {
        $serviceName = $this->services->hasServiceAlias($serviceName)
            ? $this->services->getServiceAlias($serviceName)
            : $serviceName;

        if (! $this->services->hasServiceInstance($serviceName)) {
            $this->services->setServiceInstance(
                $serviceName,
                $this->newService($serviceName)
            );
        }

        return $this->services->getServiceInstance($serviceName);
    }

    /**
     * @inheritdoc
     */
    public function hasService(string $serviceName) : bool
    {
        $serviceName = $this->services->hasServiceAlias($serviceName)
            ? $this->services->getServiceAlias($serviceName)
            : $serviceName;

        if ($this->services->hasServiceInstance($serviceName)) {
            return true;
        }

        $hasBuilderAndFactory = $this->services->hasServiceBuilder($serviceName)
            && $this->services->getServiceBuilder($serviceName)->hasServiceFactory();

        if ($hasBuilderAndFactory) {
            return true;
        }

        return $this
            ->getService(IocClassResolver::class)
            ->isClassResolvable($serviceName);
    }

    /**
     * @inheritdoc
     */
    public function newService(string $serviceName, array $arguments = []) : object
    {
        $serviceName = $this->services->hasServiceAlias($serviceName)
            ? $this->services->getServiceAlias($serviceName)
            : $serviceName;

        $circular = $this->building[$serviceName] ?? false;

        if ($circular) {
            $message = implode(", ", array_keys($this->building)) . ", $serviceName";
            throw new IocException("Circular dependency: $message");
        }

        $this->building[$serviceName] = true;

        $service = $this->services->hasServiceBuilder($serviceName)
            ? $this
                ->services
                ->getServiceBuilder($serviceName)
                ->buildService($this, $arguments)
            : $this
                ->getService(IocClassResolver::class)
                ->resolveClass($this, $serviceName, $arguments);

        unset($this->building[$serviceName]);
        return $service;
    }
}
