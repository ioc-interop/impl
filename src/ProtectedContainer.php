<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocServices;
use IocInterop\Interface\IocResolver;
use IocInterop\Impl\Resolver;

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
        $this->services->setInstance(IocContainer::class, $this);
    }

    /**
     * @inheritdoc
     */
    public function getService(string $serviceName) : object
    {
        $serviceName = $this->services->hasAlias($serviceName)
            ? $this->services->getAlias($serviceName)
            : $serviceName;

        if (! $this->services->hasInstance($serviceName)) {
            $this->services->setInstance(
                $serviceName,
                $this
                    ->services
                    ->getDefinition($serviceName)
                    ->buildInstance($this),
            );
        }

        return $this->services->getInstance($serviceName);
    }

    /**
     * @inheritdoc
     */
    public function hasService(string $serviceName) : bool
    {
        $serviceName = $this->services->hasAlias($serviceName)
            ? $this->services->getAlias($serviceName)
            : $serviceName;

        if ($this->services->hasInstance($serviceName)) {
            return true;
        }

        $hasBuilderAndFactory = $this->services->hasDefinition($serviceName)
            && $this->services->getDefinition($serviceName)->hasFactory();

        if ($hasBuilderAndFactory) {
            return true;
        }

        return $this
            ->getService(IocResolver::class)
            ->isResolvable($serviceName);
    }
}
