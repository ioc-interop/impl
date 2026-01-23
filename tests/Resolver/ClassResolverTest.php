<?php
declare(strict_types=1);

namespace IocInterop\Impl\Resolver;

use EnvInterop\Interface\EnvGetter;
use IocInterop\Impl\IocException;
use IocInterop\Impl\Fake\FakeGetEnv;
use IocInterop\Impl\Fake\FakeService;
use IocInterop\Impl\Fake\FakeServiceInterface;
use IocInterop\Impl\Fake\FakeServiceUnionType;
use IocInterop\Impl\Fake\FakeServiceWithAttributes;
use IocInterop\Impl\PublicContainer;
use IocInterop\Interface\IocContainer;
use stdClass;

class ClassResolverTest extends \PHPUnit\Framework\TestCase
{
    public function testAutowired() : void
    {
        // assemble
        $ioc = new PublicContainer();
        $classResolver = new ClassResolver();

        // act & assert
        $service = $classResolver->resolveClass($ioc, FakeService::class);
        $this->assertInstanceOf(FakeService::class, $service);
    }

    public function testAttributed() : void
    {
        // assemble
        $ioc = new PublicContainer();

        $ioc->setServiceAlias(EnvGetter::class, FakeGetEnv::class);

        $ioc->getServiceBuilder(FakeGetEnv::class)
            ->addServiceExtender(function (IocContainer $ioc, FakeGetEnv $env) {
                $env->vars = [
                    'BAZ' => 'BAZ-value',
                    'DIB' => '88'
                ];

                return $env;
            });

        $ioc->getServiceBuilder('foo')
            ->setServiceFactory(fn (IocContainer $ioc) : stdClass =>
                (object) ['value' => 'foo']);

        $ioc->getServiceBuilder('bar')
            ->setServiceFactory(fn (IocContainer $ioc) : stdClass =>
                (object) ['value' => 'bar']);

        $classResolver = new ClassResolver();
        $service = $classResolver->resolveClass($ioc, FakeServiceWithAttributes::class);
        $this->assertSame($service->foo->value, 'foo');
        $this->assertSame($service->baz, 'BAZ-value');
        $this->assertSame($service->dib, 88);
        $this->assertNull($service->gir);

        $again = $classResolver->resolveClass($ioc, FakeServiceWithAttributes::class);
        $this->assertSame($service->foo, $again->foo);
        $this->assertSame($service->baz, 'BAZ-value');
        $this->assertSame($service->dib, 88);
        $this->assertNull($service->gir);
    }

    public function testNotResolvable() : void
    {
        // assemble
        $ioc = new PublicContainer();
        $classResolver = new ClassResolver();

        // act & assert
        $this->expectException(IocException::class);
        $this->expectExceptionMessage("Service 'IocInterop\Impl\Fake\FakeServiceInterface' is not resolvable.");
        $classResolver->resolveClass($ioc, FakeServiceInterface::class);
    }

    public function testBroken() : void
    {
        // assemble
        $ioc = new PublicContainer();
        $classResolver = new ClassResolver();

        // act & assert
        $this->expectException(IocException::class);
        $this->expectExceptionMessage("Cannot resolve parameter for IocInterop\Impl\Fake\FakeServiceUnionType::__construct(SplFileObject|stdClass \$dependency)");
        $classResolver->resolveClass($ioc, FakeServiceUnionType::class);
    }
}
