<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use EnvInterop\Interface\EnvGetter;

class FakeGetEnv implements EnvGetter
{
    /**
     * @var array<string,string>
     */
    public array $vars = [];

    public function getEnv(string $name) : ?string
    {
        return $this->vars[$name] ?? null;
    }
}
