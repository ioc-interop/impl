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
        IocClassResolver $classResolver = new ClassResolver(),
    ) {
        parent::__construct($classResolver);
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

        $this->instances[$serviceName] ??= $this
            ->getServiceBuilder($serviceName)
            ->buildService($this);

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
            ->isClassResolvable($serviceName);
    }
}
