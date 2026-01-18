<?php
declare(strict_types=1);

namespace IocInterop\Impl\Resolver;

use IocInterop\Interface\IocContainer;
use IocInterop\Interface\Resolver\IocParameterResolver;
use IocInterop\Interface\Resolver\IocParametersResolver;

class ParametersResolver implements IocParametersResolver
{
    public function __construct(
        protected IocParameterResolver $parameterResolver = new ParameterResolver(),
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolveParameters(
        IocContainer $ioc,
        array $parameters,
        array $args = [],
    ) : array
    {
        foreach ($parameters as $parameter) {
            $parameterName = $parameter->getName();

            if (array_key_exists($parameterName, $args)) {
                continue;
            }

            $args[$parameterName] = $this
                ->parameterResolver
                ->resolveParameter(
                    $ioc,
                    $parameter,
                );
        }

        return $args;
    }
}
