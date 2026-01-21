<?php
declare(strict_types=1);

namespace IocInterop\Impl\Fake;

use IocInterop\Impl\IocException;
use IocInterop\Impl\Resolver\ClassResolver;
use IocInterop\Interface\IocContainer;
use IocInterop\Interface\Resolver\IocClassResolver;

class FakeContainer implements IocContainer
{
    protected IocClassResolver $classResolver;

    public function __construct()
    {
        $this->classResolver = new ClassResolver();
    }

    /**
     * @inheritdoc
     */
    public function getService(string $serviceName) : object
    {
        if ($serviceName === IocClassResolver::class) {
            return $this->classResolver;
        }

        throw new IocException("Null container");
    }

    /**
     * @inheritdoc
     */
    public function hasService(string $serviceName) : bool
    {
        throw new IocException("Null container");
    }

    /**
     * @inheritdoc
     */
    public function newService(string $serviceName, array $serviceArgs = []) : object
    {
        throw new IocException("Null container");
    }
}
