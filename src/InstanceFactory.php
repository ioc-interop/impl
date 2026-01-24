<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocInstanceFactory;
use IocInterop\Interface\IocResolver;

class InstanceFactory implements IocInstanceFactory
{
    public function __construct(
        protected IocContainer $ioc,
        protected IocResolver $resolver = new Resolver(),
    ) {
    }

    /**
     * @inheritdoc
     */
    public function newInstance(
        string $class,
        array $arguments = [],
    ) : object
    {
        return $this->resolver->resolve(
            $this->ioc,
            $class,
            $arguments,
        );
    }
}
