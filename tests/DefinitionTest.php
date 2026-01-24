<?php
declare(strict_types=1);

namespace IocInterop\Impl;

use IocInterop\Impl\Fake\FakeContainer;
use IocInterop\Impl\Resolver;
use IocInterop\Interface\IocContainer;
use stdClass;

class DefinitionTest extends \PHPUnit\Framework\TestCase
{
    protected Resolver $resolver;

    protected function setUp() : void
    {
        $this->resolver = new Resolver();
    }

    protected function newDefinition(string $name) : Definition
    {
        return new Definition($name);
    }

    public function testFactory() : void
    {
        $name = stdClass::class;
        $factory = fn (IocContainer $ioc) : stdClass => new stdClass();
        $definition = $this->newDefinition($name);
        $ioc = new FakeContainer();

        $this->assertFalse($definition->hasFactory());
        $definition->setFactory($factory);
        $this->assertTrue($definition->hasFactory());
        $this->assertSame($factory, $definition->getFactory());

        $actual = $definition->buildInstance($ioc);
        $this->assertInstanceOf($name, $actual);
        $again = $definition->buildInstance($ioc);
        $this->assertNotSame($actual, $again);

        $definition->unsetFactory();
        $this->assertFalse($definition->hasFactory());
        $this->expectException(IocException::class);
        $this->expectExceptionMessage("Builder for 'stdClass' has no factory.");
        $definition->getFactory();
    }

    public function testExtenders() : void
    {
        $name = stdClass::class;
        $factory = fn (IocContainer $ioc) : stdClass => new stdClass();
        $definition = $this->newDefinition($name);
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

        $this->assertFalse($definition->hasExtenders());
        $definition->setExtenders($extenders);
        $this->assertTrue($definition->hasExtenders());
        $this->assertSame($extenders, $definition->getExtenders());

        $actual = $definition->buildInstance($ioc);
        $this->assertInstanceOf($name, $actual);
        $this->assertTrue($actual->ext1);
        $this->assertTrue($actual->ext2);
        $this->assertTrue($actual->ext3);

        $again = $definition->buildInstance($ioc);
        $this->assertNotSame($actual, $again);

        $definition->unsetExtenders();
        $this->assertFalse($definition->hasExtenders());
    }
}
