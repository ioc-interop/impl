<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocServices;

/**
 * A container of predefined services that cannot be reset in-flight.
 */
class ProtectedContainer implements IocContainer
{
    /**
     * @var array<string,bool>
     */
    protected array $instantiating = [];

    public function __construct(protected IocServices $services)
    {
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

        return $this->services->hasServiceInstance($serviceName)
            || $this->services->hasServiceFactory($serviceName);
    }

    /**
     * @inheritdoc
     */
    public function newService(string $serviceName) : object
    {
        $serviceName = $this->services->hasServiceAlias($serviceName)
            ? $this->services->getServiceAlias($serviceName)
            : $serviceName;

        return $this->instantiate($serviceName);
    }

    protected function instantiate(string $serviceName) : object
    {
        $circular = $this->instantiating[$serviceName] ?? false;

        if ($circular) {
            throw new ContainerException("Circular dependency");
        }

        $this->instantiating[$serviceName] = true;
        $serviceFactory = $this->services->getServiceFactory($serviceName);
        $instance = $serviceFactory($this);
        unset($this->instantiating[$serviceName]);
        return $instance;
    }
}
