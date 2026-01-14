# ioc-interop/impl

[![PDS Skeleton](https://img.shields.io/badge/pds-skeleton-blue.svg?style=flat-square)](https://github.com/php-pds/skeleton)
[![PDS Composer Script Names](https://img.shields.io/badge/pds-composer--script--names-blue?style=flat-square)](https://github.com/php-pds/composer-script-names)

This package offers two autowiring _IocContainer_ reference implementations:

- a _PublicContainer_ where the manually-defined services are publicly
  modifiable;

- a _ProtectedContainer_ where the manually-predefined services are protected
  inside the container; and,

- an _AutowiredContainer_ that extends _ProtectedContainer_ so that undefined
  services are automatically resolved.
