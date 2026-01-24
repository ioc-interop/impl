<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocResolver;
use IocInterop\Impl\Resolver;

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
        IocResolver $resolver = new Resolver(),
    ) {
        parent::__construct($resolver);
        $this->setInstance(IocContainer::class, $this);
    }

    /**
     * @inheritdoc
     */
    public function getService(string $serviceName) : object
    {
        $serviceName = $this->hasAlias($serviceName)
            ? $this->getAlias($serviceName)
            : $serviceName;

        $this->instances[$serviceName] ??= $this
            ->getDefinition($serviceName)
            ->buildInstance($this);

        return $this->instances[$serviceName];
    }

    /**
     * @inheritdoc
     */
    public function hasService(string $serviceName) : bool
    {
        $serviceName = $this->hasAlias($serviceName)
            ? $this->getAlias($serviceName)
            : $serviceName;

        if ($this->hasInstance($serviceName)) {
            return true;
        }

        $hasBuilderAndFactory = $this->hasDefinition($serviceName)
            && $this->getDefinition($serviceName)->hasFactory();

        if ($hasBuilderAndFactory) {
            return true;
        }

        return $this
            ->getService(IocResolver::class)
            ->isResolvable($serviceName);
    }
}
