<?php
declare(strict_types=1);

namespace IocInterop\Impl\Fake;

use IocInterop\Impl\IocException;
use IocInterop\Impl\Resolver;
use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocResolver;

class FakeContainer implements IocContainer
{
    protected IocResolver $resolver;

    public function __construct()
    {
        $this->resolver = new Resolver();
    }

    /**
     * @inheritdoc
     */
    public function getService(string $serviceName) : object
    {
        if ($serviceName === IocResolver::class) {
            return $this->resolver;
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
}
