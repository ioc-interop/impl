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
    protected array $building = [];

    public function __construct(protected IocServices $services = new Services())
    {
        $this->services->setServiceInstance(IocContainer::class, $this);
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
     *
     * @todo is there any case where we *cannot* create an instance?
     */
    public function hasService(string $serviceName) : bool
    {
        $serviceName = $this->services->hasServiceAlias($serviceName)
            ? $this->services->getServiceAlias($serviceName)
            : $serviceName;

        return $this->services->hasServiceInstance($serviceName)
            || $this->services->getServiceBuilder($serviceName)->isServiceBuildable();
    }

    /**
     * @inheritdoc
     */
    public function newService(string $serviceName, array $serviceArgs = []) : object
    {
        $serviceName = $this->services->hasServiceAlias($serviceName)
            ? $this->services->getServiceAlias($serviceName)
            : $serviceName;

        $circular = $this->building[$serviceName] ?? false;

        if ($circular) {
            $message = implode(", ", array_keys($this->building)) . ", $serviceName";
            throw new ContainerException("Circular dependency: $message");
        }

        $this->building[$serviceName] = true;

        $instance = $this
            ->services
            ->getServiceBuilder($serviceName)
            ->buildService($this, $serviceArgs);

        unset($this->building[$serviceName]);
        return $instance;
    }
}
