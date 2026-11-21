<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocContainerFactory;

class ContainerFactory implements IocContainerFactory
{
    /**
     * @var array<array-key, object>
     */
    public array $instances = [];

    /**
     * @var array<array-key, callable(IocContainer):object>
     */
    public array $factories = [];

    public function newContainer() : IocContainer
    {
        return new Container($this->instances, $this->factories);
    }
}
