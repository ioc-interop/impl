<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Impl\Fake\FakeContainer;
use IocInterop\Impl\Resolver\ClassResolver;
use IocInterop\Interface\IocContainer;
use stdClass;

class ServiceBuilderTest extends \PHPUnit\Framework\TestCase
{
    protected ClassResolver $classResolver;

    protected function setUp() : void
    {
        $this->classResolver = new ClassResolver();
    }

    protected function newServiceBuilder(string $name) : ServiceBuilder
    {
        return new ServiceBuilder($name);
    }

    public function testServiceFactory() : void
    {
        $name = stdClass::class;
        $factory = fn (IocContainer $ioc) : stdClass => new stdClass();
        $serviceBuilder = $this->newServiceBuilder($name);
        $ioc = new FakeContainer();

        $this->assertFalse($serviceBuilder->hasServiceFactory());
        $serviceBuilder->setServiceFactory($factory);
        $this->assertTrue($serviceBuilder->hasServiceFactory());
        $this->assertSame($factory, $serviceBuilder->getServiceFactory());

        $actual = $serviceBuilder->buildService($ioc);
        $this->assertInstanceOf($name, $actual);
        $again = $serviceBuilder->buildService($ioc);
        $this->assertNotSame($actual, $again);

        $serviceBuilder->unsetServiceFactory();
        $this->assertFalse($serviceBuilder->hasServiceFactory());
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage("Builder for 'stdClass' has no factory.");
        $serviceBuilder->getServiceFactory();
    }

    public function testServiceFactoryWithArgs() : void
    {
        $name = stdClass::class;
        $factory = fn (IocContainer $ioc, array $args = []) : stdClass => (object) $args;
        $serviceBuilder = $this->newServiceBuilder($name);
        $serviceBuilder->setServiceFactory($factory);
        $ioc = new FakeContainer();
        $expect = (object) ['foo' => 'bar'];
        $actual = $serviceBuilder->buildService($ioc, ['foo' => 'bar']);
        $this->assertEquals($expect, $actual);
    }

    public function testServiceExtenders() : void
    {
        $name = stdClass::class;
        $factory = fn (IocContainer $ioc) : stdClass => new stdClass();
        $serviceBuilder = $this->newServiceBuilder($name);
        $ioc = new FakeContainer();

        $extenders = [
            function (IocContainer $ioc, stdClass $object) : stdClass {
                $object->ext1 = true;
                return $object;
            },
            function (IocContainer $ioc, stdClass $object) : stdClass {
                $object->ext2 = true;
                return $object;
            },
            function (IocContainer $ioc, stdClass $object) : stdClass {
                $object->ext3 = true;
                return $object;
            },
        ];

        $this->assertFalse($serviceBuilder->hasServiceExtenders());
        $serviceBuilder->setServiceExtenders($extenders);
        $this->assertTrue($serviceBuilder->hasServiceExtenders());
        $this->assertSame($extenders, $serviceBuilder->getServiceExtenders());

        $actual = $serviceBuilder->buildService($ioc);
        $this->assertInstanceOf($name, $actual);
        $this->assertTrue($actual->ext1);
        $this->assertTrue($actual->ext2);
        $this->assertTrue($actual->ext3);

        $again = $serviceBuilder->buildService($ioc);
        $this->assertNotSame($actual, $again);

        $serviceBuilder->unsetServiceExtenders();
        $this->assertFalse($serviceBuilder->hasServiceExtenders());
    }
}
