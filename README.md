# ioc-interop/impl

[![PDS Skeleton](https://img.shields.io/badge/pds-skeleton-blue.svg?style=flat-square)](https://github.com/php-pds/skeleton)
[![PDS Composer Script Names](https://img.shields.io/badge/pds-composer--script--names-blue?style=flat-square)](https://github.com/php-pds/composer-script-names)

Reference implementations of the [Ioc-Interop][] interfaces for PHP 8.4+.

## Installation

```
composer require ioc-interop/impl
```

## Usage

Compose a container by populating a [_IocContainerFactory_][] with shared
instances and service factories, then ask it for a new container:

```php
use IocInterop\Impl\ContainerFactory;
use IocInterop\Interface\IocContainer;

$containerFactory = new ContainerFactory();

$containerFactory->instances = [
    PDO::class => new PDO('sqlite::memory:'),
];

$containerFactory->factories = [
    Logger::class => fn (IocContainer $ioc) => new Logger(),
    UserService::class => fn (IocContainer $ioc) => new UserService(
        $ioc->getService(PDO::class),
        $ioc->getService(Logger::class),
    ),
];

$ioc = $containerFactory->newContainer();
```

Retrieve services by name. The first call to `getService()` resolves and
shares the instance for subsequent calls:

```php
$logger = $ioc->getService(Logger::class);
$users = $ioc->getService(UserService::class);
```

Check for service availability:

```php
if ($ioc->hasService(Logger::class)) {
    // ...
}
```

Requesting a service that is not registered throws a `ContainerException`:

```php
use IocInterop\Interface\IocThrowable;

try {
    $ioc->getService('does-not-exist');
} catch (IocThrowable $e) {
    // ...
}
```

## Classes

| Interface              | Implementation       |
| ---------------------- | -------------------- |
| _IocContainer_         | `Container`          |
| _IocContainerFactory_  | `ContainerFactory`   |
| _IocThrowable_         | `ContainerException` |

All classes are in the `IocInterop\Impl` namespace.

See the [Ioc-Interop][] interface package for the full specification.

[Ioc-Interop]: https://github.com/ioc-interop/interface
[_IocContainerFactory_]: https://github.com/ioc-interop/interface#ioccontainerfactory
