<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocParameterResolver;
use IocInterop\Interface\IocParametersResolver;

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
        array $arguments = [],
    ) : array
    {
        foreach ($parameters as $parameter) {
            $parameterName = $parameter->getName();

            if (array_key_exists($parameterName, $arguments)) {
                continue;
            }

            $arguments[$parameterName] = $this
                ->parameterResolver
                ->resolveParameter(
                    $ioc,
                    $parameter,
                );
        }

        return $arguments;
    }
}
