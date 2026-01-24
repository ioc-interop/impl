<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use EnvInterop\Interface\EnvGetter;
use IocInterop\Impl\Fake\FakeGetEnv;
use IocInterop\Impl\Fake\FakeService;
use IocInterop\Impl\Fake\FakeServiceInterface;
use IocInterop\Impl\Fake\FakeServiceUnionType;
use IocInterop\Impl\Fake\FakeServiceWithAttributes;
use IocInterop\Impl\IocException;
use IocInterop\Impl\PublicContainer;
use IocInterop\Interface\IocContainer;
use stdClass;

class ResolverTest extends \PHPUnit\Framework\TestCase
{
    public function testAutowired() : void
    {
        // assemble
        $ioc = new PublicContainer();
        $resolver = new Resolver();

        // act & assert
        $service = $resolver->resolve($ioc, FakeService::class);
        $this->assertInstanceOf(FakeService::class, $service);
    }

    public function testAttributed() : void
    {
        // assemble
        $ioc = new PublicContainer();

        $ioc->setAlias(EnvGetter::class, FakeGetEnv::class);

        $ioc->getDefinition(FakeGetEnv::class)
            ->addExtender(function (IocContainer $ioc, FakeGetEnv $env) {
                $env->vars = [
                    'BAZ' => 'BAZ-value',
                    'DIB' => '88'
                ];

                return $env;
            });

        $ioc->getDefinition('foo')
            ->setFactory(fn (IocContainer $ioc) : stdClass =>
                (object) ['value' => 'foo']);

        $ioc->getDefinition('bar')
            ->setFactory(fn (IocContainer $ioc) : stdClass =>
                (object) ['value' => 'bar']);

        $resolver = new Resolver();
        $service = $resolver->resolve($ioc, FakeServiceWithAttributes::class);
        $this->assertSame($service->foo->value, 'foo');
        $this->assertSame($service->baz, 'BAZ-value');
        $this->assertSame($service->dib, 88);
        $this->assertNull($service->gir);

        $again = $resolver->resolve($ioc, FakeServiceWithAttributes::class);
        $this->assertSame($service->foo, $again->foo);
        $this->assertSame($service->baz, 'BAZ-value');
        $this->assertSame($service->dib, 88);
        $this->assertNull($service->gir);
    }

    public function testNotResolvable() : void
    {
        // assemble
        $ioc = new PublicContainer();
        $resolver = new Resolver();

        // act & assert
        $this->expectException(IocException::class);
        $this->expectExceptionMessage("Service 'IocInterop\Impl\Fake\FakeServiceInterface' is not resolvable.");
        $resolver->resolve($ioc, FakeServiceInterface::class);
    }

    public function testBroken() : void
    {
        // assemble
        $ioc = new PublicContainer();
        $resolver = new Resolver();

        // act & assert
        $this->expectException(IocException::class);
        $this->expectExceptionMessage("Cannot resolve parameter for IocInterop\Impl\Fake\FakeServiceUnionType::__construct(SplFileObject|stdClass \$dependency)");
        $resolver->resolve($ioc, FakeServiceUnionType::class);
    }

    public function testOverrideParameters() : void
    {
        // assemble
        $ioc = new PublicContainer();
        $resolver = new Resolver();

        // act & assert
        $actual = $resolver->resolve($ioc, FakeService::class, [
            'nonNamedType' => 'override',
        ]);

        $this->assertSame('override', $actual->nonNamedType);
    }
}
