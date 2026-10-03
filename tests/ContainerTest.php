<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Impl\FakeService;
use IocInterop\Interface\IocContainer;
use IocInterop\Interface\IocThrowable;
use LogicException;
use stdClass;
use TypeError;

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    public function newContainer() : IocContainer
    {
        $containerFactory = new ContainerFactory();

        $containerFactory->instances = [stdClass::class => new stdClass()];

        $containerFactory->factories = [
            FakeService::class => fn (IocContainer $ioc)
                => new FakeService(dependency: $ioc->getService(stdClass::class)),
            'foo' => fn (IocContainer $ioc) => (object) ['bar' => 'baz'],
        ];

        return $containerFactory->newContainer();
    }

    public function testHasService() : void
    {
        $ioc = $this->newContainer();
        $this->assertTrue($ioc->hasService(stdClass::class));
        $this->assertTrue($ioc->hasService(FakeService::class));
        $this->assertTrue($ioc->hasService('foo'));
        $this->assertFalse($ioc->hasService('noSuchService'));
    }

    public function testGetService() : void
    {
        $ioc = $this->newContainer();
        $stdClass = $ioc->getService(stdClass::class);
        $fakeService = $ioc->getService(FakeService::class);
        $this->assertSame($stdClass, $fakeService->dependency);

        /** @var object{bar: string} $foo */
        $foo = $ioc->getService('foo');
        $this->assertSame('baz', $foo->bar);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Service not available: noSuchService');
        $ioc->getService('noSuchService');
    }

    public function testFactoryResultIsSharedAcrossCalls() : void
    {
        $ioc = $this->newContainer();

        $this->assertSame(
            $ioc->getService(FakeService::class),
            $ioc->getService(FakeService::class),
        );
    }

    public function testMissThrowsIocThrowable() : void
    {
        $ioc = $this->newContainer();

        $this->expectException(IocThrowable::class);
        $ioc->getService('noSuchService');
    }

    public function testFactoryThrowableIsWrapped() : void
    {
        $containerFactory = new ContainerFactory();

        $containerFactory->factories = [
            'broken' => fn (IocContainer $ioc)
                => throw new LogicException('bad config'),
        ];

        $ioc = $containerFactory->newContainer();

        try {
            $ioc->getService('broken');
            $this->fail('Expected an IocThrowable.');
        } catch (IocThrowable $e) {
            $this->assertInstanceOf(ContainerException::class, $e);
            $this->assertSame('Service factory failed: broken', $e->getMessage());
            $this->assertInstanceOf(LogicException::class, $e->getPrevious());
        }
    }

    public function testNestedMissRetainsItsIocThrowable() : void
    {
        $containerFactory = new ContainerFactory();

        $containerFactory->factories = [
            'needsMissing' => fn (IocContainer $ioc)
                => $ioc->getService('noSuchService'),
        ];

        $ioc = $containerFactory->newContainer();

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Service not available: noSuchService');
        $ioc->getService('needsMissing');
    }

    public function testCircularDependencyThrowsIocThrowable() : void
    {
        $containerFactory = new ContainerFactory();

        $containerFactory->factories = [
            'a' => fn (IocContainer $ioc)
                => (object) ['b' => $ioc->getService('b')],
            'b' => fn (IocContainer $ioc)
                => (object) ['a' => $ioc->getService('a')],
        ];

        $ioc = $containerFactory->newContainer();

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Circular service dependency: a -> b -> a');
        $ioc->getService('a');
    }

    public function testCircularFailureDoesNotPoisonLaterCalls() : void
    {
        $containerFactory = new ContainerFactory();

        $containerFactory->instances = [stdClass::class => new stdClass()];

        $containerFactory->factories = [
            'a' => fn (IocContainer $ioc)
                => (object) ['b' => $ioc->getService('b')],
            'b' => fn (IocContainer $ioc)
                => (object) ['a' => $ioc->getService('a')],
            'ok' => fn (IocContainer $ioc) => $ioc->getService(stdClass::class),
        ];

        $ioc = $containerFactory->newContainer();

        try {
            $ioc->getService('a');
            $this->fail('Expected an IocThrowable.');
        } catch (IocThrowable $e) {
            // expected; the guard must clear its state on the way out
        }

        $this->assertSame(
            $ioc->getService(stdClass::class),
            $ioc->getService('ok'),
        );
    }

    public function testFactoryErrorIsWrapped() : void
    {
        $containerFactory = new ContainerFactory();

        $containerFactory->factories = [
            'broken' => fn (IocContainer $ioc) => throw new TypeError('bad type'),
        ];

        $ioc = $containerFactory->newContainer();

        try {
            $ioc->getService('broken');
            $this->fail('Expected an IocThrowable.');
        } catch (IocThrowable $e) {
            $this->assertSame('Service factory failed: broken', $e->getMessage());
            $this->assertInstanceOf(TypeError::class, $e->getPrevious());
        }
    }

    public function testFactoryNonObjectIsWrapped() : void
    {
        $containerFactory = new ContainerFactory();

        /** @phpstan-ignore assign.propertyType */
        $containerFactory->factories = [
            'liar' => fn (IocContainer $ioc) => 'not an object',
        ];

        $ioc = $containerFactory->newContainer();

        try {
            $ioc->getService('liar');
            $this->fail('Expected an IocThrowable.');
        } catch (IocThrowable $e) {
            $this->assertSame('Service factory failed: liar', $e->getMessage());
            $this->assertInstanceOf(TypeError::class, $e->getPrevious());
        }
    }

    public function testNewContainerReturnsDistinctContainers() : void
    {
        $containerFactory = new ContainerFactory();

        $containerFactory->instances = [stdClass::class => new stdClass()];

        $containerFactory->factories = [
            FakeService::class => fn (IocContainer $ioc) => new FakeService(),
        ];

        $first = $containerFactory->newContainer();
        $second = $containerFactory->newContainer();

        $this->assertNotSame($first, $second);

        // sharing is this implementation's retrieval logic, not a directive
        $this->assertSame(
            $first->getService(stdClass::class),
            $second->getService(stdClass::class),
        );

        $this->assertNotSame(
            $first->getService(FakeService::class),
            $second->getService(FakeService::class),
        );
    }

    public function testFalseFromHasServiceGuaranteesFailure() : void
    {
        $ioc = $this->newContainer();

        $this->assertFalse($ioc->hasService('noSuchService'));
        $this->expectException(IocThrowable::class);
        $ioc->getService('noSuchService');
    }

    public function testTrueFromHasServiceDoesNotGuaranteeSuccess() : void
    {
        $containerFactory = new ContainerFactory();

        $containerFactory->factories = [
            'broken' => fn (IocContainer $ioc) => throw new LogicException('nope'),
        ];

        $ioc = $containerFactory->newContainer();

        // the failure is not knowable without attempting retrieval
        $this->assertTrue($ioc->hasService('broken'));
        $this->expectException(IocThrowable::class);
        $ioc->getService('broken');
    }

    public function testNonObjectInstanceIsWrapped() : void
    {
        $containerFactory = new ContainerFactory();

        /** @phpstan-ignore assign.propertyType */
        $containerFactory->instances = ['liar' => 'not an object'];

        $ioc = $containerFactory->newContainer();

        try {
            $ioc->getService('liar');
            $this->fail('Expected an IocThrowable.');
        } catch (IocThrowable $e) {
            $this->assertSame('Service retrieval failed: liar', $e->getMessage());
            $this->assertInstanceOf(TypeError::class, $e->getPrevious());
        }
    }

    public function testHasServiceDoesNotProduceTheService() : void
    {
        $produced = false;
        $containerFactory = new ContainerFactory();

        $containerFactory->factories = [
            'counted' => function (
                IocContainer $ioc,
            ) use (&$produced) {
                $produced = true;

                return new stdClass();
            },
        ];

        $ioc = $containerFactory->newContainer();

        $this->assertTrue($ioc->hasService('counted'));
        $this->assertFalse($produced);
    }
}
