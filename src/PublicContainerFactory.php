<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocContainerFactory;
use IocInterop\Interface\IocProvider;

class PublicContainerFactory implements IocContainerFactory
{
    /**
     * @param ?IocProvider $provider A "seed" provider that can call other
     * providers as needed.
     */
    public function __construct(
        protected ?IocProvider $provider = null
    ) {
    }

    public function newContainer() : IocContainer
    {
        $container = new PublicContainer();
        $this->provider?->provideServices($container);
        return $container;
    }
}
