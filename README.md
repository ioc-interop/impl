# ioc-interop/impl

[![PDS Skeleton](https://img.shields.io/badge/pds-skeleton-blue.svg?style=flat-square)](https://github.com/php-pds/skeleton)
[![PDS Composer Script Names](https://img.shields.io/badge/pds-composer--script--names-blue?style=flat-square)](https://github.com/php-pds/composer-script-names)

This package offers a reference implementation all the Ioc-Interop interface
to present a typical "open" autowired container.

## Installation

```
$ composer require ioc-interop/impl
```

## Getting Started

```php
use IocInterop\Impl\Container;
use IocInterop\Interface\IocContainer;

$ioc = new Container();
```

## Getting Services

```php
$foo = $ioc->getService(Foo::class);
```

## Setting Service Instances

```php
$ioc->setService(Foo::class, new Foo());
```

## Setting Service Aliases

```php
$ioc->setAlias('foo.foo', Foo::class);
$foo = $ioc->getService('foo.foo');
```

## Defining Services

### Service Factory

```php
$ioc->getDefinition(Foo::class)
    ->setFactory(fn (IocContainer $ioc) : Foo => return new Foo());
```

### Service Extenders

```php
$ioc->getDefinition(Foo::class)
    ->addExtender(function (IocContainer $ioc, Foo $foo) : Foo {
        $foo->bar = 'baz';
        return $foo;
    });
```
