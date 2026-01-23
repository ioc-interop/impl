<?php
declare(strict_types=1);

namespace IocInterop\Impl\Resolver;

use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocInstanceFactory;
use IocInterop\Interface\Resolver\IocClassResolver;

class InstanceFactory implements IocInstanceFactory
{
    public function __construct(
        protected IocContainer $ioc,
        protected IocClassResolver $classResolver = new ClassResolver(),
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
        return $this->classResolver->resolveClass(
            $this->ioc,
            $class,
            $arguments,
        );
    }
}
