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
    protected array $building = [];

    public function __construct(
        protected ServiceResolver $serviceResolver = new ServiceResolver(),
    ) {
        parent::__construct($serviceResolver);
        $this->setServiceInstance(IocContainer::class, $this);
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
     *
     * @todo is there any case where we *cannot* create an instance?
     */
    public function hasService(string $serviceName) : bool
    {
        $serviceName = $this->hasServiceAlias($serviceName)
            ? $this->getServiceAlias($serviceName)
            : $serviceName;

        return $this->hasServiceInstance($serviceName)
            || $this->getServiceBuilder($serviceName)->isServiceBuildable();
    }

    /**
     * @inheritdoc
     */
    public function newService(string $serviceName) : object
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

        $instance = $this
            ->getServiceBuilder($serviceName)
            ->buildService($this);

        unset($this->building[$serviceName]);
        return $instance;
    }
}
