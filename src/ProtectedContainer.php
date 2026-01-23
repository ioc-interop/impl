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
    ) {
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
                $this
                    ->services
                    ->getServiceBuilder($serviceName)
                    ->buildService($this),
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
}
