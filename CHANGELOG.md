# Change Log

## 1.0.0

First stable release.

- Require `ioc-interop/interface` at `^1.0`.

- Widened the `Container` and `ContainerFactory` registry key types from
  `string` to `array-key`, so that numeric service names pass static analysis.

- `newService()` now looks up the factory before marking the service as
  resolving, so a failed lookup no longer leaves the marker set.

- Strengthened the circular-dependency cleanup test, and added a test for
  numeric service names.

- Documentation fixes.

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
  `newContainer()` call returns a new `Container` seeded with the
  factory's registries. Pre-built instances are shared across containers;
  factory-built services are per container.

- `ContainerException extends RuntimeException implements IocThrowable`.

- Consumes `ioc-interop/interface` from Packagist via `1.x@dev`.
