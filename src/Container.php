<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocThrowable;
use Throwable;

class Container implements IocContainer
{
    /**
     * @var array<string, true>
     */
    protected array $resolving = [];

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

        try {
            $this->instances[$serviceName] ??= $this->newService($serviceName);

            return $this->instances[$serviceName];
        } catch (IocThrowable $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new ContainerException(
                "Service retrieval failed: {$serviceName}",
                previous: $e,
            );
        }
    }

    protected function newService(string $serviceName) : object
    {
        if (isset($this->resolving[$serviceName])) {
            $path = implode(
                ' -> ',
                [...array_keys($this->resolving), $serviceName],
            );

            throw new ContainerException("Circular service dependency: {$path}");
        }

        $this->resolving[$serviceName] = true;
        $factory = $this->factories[$serviceName];

        try {
            return $factory($this);
        } catch (IocThrowable $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new ContainerException(
                "Service factory failed: {$serviceName}",
                previous: $e,
            );
        } finally {
            unset($this->resolving[$serviceName]);
        }
    }
}
