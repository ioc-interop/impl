<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;

class Container implements IocContainer
{
    /**
     * @param array<string, object> $instances
     * @param array<string, callable(IocContainer):object> $factories
     */
    public function __construct(
        protected array $instances = [],
        protected array $factories = [],
    ) {
    }

    /**
     * @inheritdoc
     */
    public function hasService(string $serviceName) : bool
    {
        return isset($this->instances[$serviceName])
            || isset($this->factories[$serviceName]);
    }

    /**
     * @inheritdoc
     */
    public function getService(string $serviceName) : object
    {
        if (! $this->hasService($serviceName)) {
            throw new ContainerException("Service not available: {$serviceName}");
        }

        $this->instances[$serviceName] ??= $this->newService($serviceName);
        return $this->instances[$serviceName];
    }

    protected function newService(string $serviceName) : object
    {
        $factory = $this->factories[$serviceName];
        return $factory($this);
    }
}
