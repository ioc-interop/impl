<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;

/**
 * Typical container that allows in-flight resetting of services.
 */
class PublicContainer extends Services implements IocContainer
{
    /**
     * @var array<string,bool>
     */
    protected array $instantiating = [];

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

        return $this->hasServiceInstance($serviceName)
            || $this->hasServiceFactory($serviceName);
    }

    /**
     * @inheritdoc
     */
    public function newService(string $serviceName) : object
    {
        $serviceName = $this->hasServiceAlias($serviceName)
            ? $this->getServiceAlias($serviceName)
            : $serviceName;

        $circular = $this->instantiating[$serviceName] ?? false;

        if ($circular) {
            throw new ContainerException("Circular dependency");
        }

        $this->instantiating[$serviceName] = true;
        $serviceFactory = $this->getServiceFactory($serviceName);
        $instance = $serviceFactory($this);
        unset($this->instantiating[$serviceName]);
        return $instance;
    }
}
