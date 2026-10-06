# Change Log

## 1.0.0-beta1

First public release.

- `Container` implements `IocContainer` with constructor-supplied
  `$instances` and `$factories` registries; `hasService()` is a pure
  lookup against both, and `getService()` checks instances first, then
  runs an unresolved factory once and caches the result.

- `getService()` wraps any `Throwable` a factory raises (`Error` or
  `Exception` alike) as `ContainerException` with `$previous` set, but
  passes an already-`IocThrowable` through unchanged.

- `getService()` detects a service that, directly or indirectly, depends
  on itself, and throws `ContainerException` describing the circular
  path.

- `ContainerFactory` implements `IocContainerFactory`; every
  `newContainer()` call returns a new `Container` sharing the factory's
  registries.

- `ContainerException extends RuntimeException implements IocThrowable`.

- Consumes `ioc-interop/interface` from Packagist via `1.x@dev`.
