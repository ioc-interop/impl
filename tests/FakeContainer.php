<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocServiceResolver;

class FakeContainer implements IocContainer
{
    protected IocServiceResolver $serviceResolver;

    public function __construct()
    {
        $this->serviceResolver = new ServiceResolver();
    }

    /**
     * @inheritdoc
     */
    public function getService(string $serviceName) : object
    {
        if ($serviceName === IocServiceResolver::class) {
            return $this->serviceResolver;
        }

        throw new ContainerException("Null container");
    }

    /**
     * @inheritdoc
     */
    public function hasService(string $serviceName) : bool
    {
        throw new ContainerException("Null container");
    }

    /**
     * @inheritdoc
     */
    public function newService(string $serviceName) : object
    {
        throw new ContainerException("Null container");
    }
}
