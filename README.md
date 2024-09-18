# bitpanda/cache-utils

[![CI][1]][2]

**[What is it for?](#what-is-it-for)** |
**[What are the requirements?](#what-are-the-requirements)** |
**[How to install it?](#how-to-install-it)** |
**[How to use it?](#how-to-use-it)** |
**[How to contribute?](#how-to-contribute)**

Cache utils is a set of caching tools and cache strategies to build effective
caching solutions in your application.

## What is it for?

### Cache Stack

The Cache Stack allows you to specify a hierarchy of simple cache
implementations, creating a layered caching system.

This strategy optimizes retrieval times by ensuring frequently accessed data is
quickly available from higher cache levels while providing a robust fallback
mechanism for less frequently accessed data. It enhances efficiency and
performance by minimizing the load on lower-level caches and external data
sources.

#### Key Features

- Hierarchical Cache Levels: The cache is organized into multiple levels, with
  each level being a simple cache implementation.
- Cache Miss Handling: When a cache miss occurs at the top level, the system
  attempts to retrieve the value from the next level in the hierarchy,
  continuing this process until a cache hit is achieved or all levels are
  exhausted.
- Fallback Mechanism: If the value is not found in any cache level, the system
  uses a provided fallback mechanism to obtain the value.
- Automatic Population: When a cache hit occurs at any level, the retrieved
  value is automatically populated in all higher levels of the cache hierarchy.
  This ensures that subsequent requests for the same key will hit higher cache
  levels, improving performance.

### Stale-While-Revalidate (SWR)

This repository offers a Stale-While-Revalidate (SWR) caching strategy to ensure
high availability and improved user experience.

The SWR strategy is useful for providing quick responses and maintaining data
availability even when the underlying data source is temporarily unavailable.

#### Key Features

- Expiration Date: Cached items have a set expiration date or time-to-live
  (TTL).
- Stale Data: When data becomes stale, it is not immediately discarded.
- Background Revalidation: An attempt to fetch fresh data is made in the
  background when stale data is accessed.
- Fallback: If fresh data cannot be fetched, the stale data is still served as a
  fallback to ensure availability.

### Other Utilities

This package also contains a set of other useful adapters for PSR16 caches and
SWR revalidators.

#### Default TTL Adapter

Allows setting a default TTL for caches when none or null is provided. This can
be helpful when the ttl can not be determined or when an infinite storage of the
cache item needs to be prevented.

#### Key Suffix Adapter

Automatically attaches a suffix to any given key. Helpful to ensure that there
are no naming collisions between components that may use the same cache keys,
but have no defined key sharing usecase.

#### Fault Tolerancy Adapter

Any cache action is wrapped in a try/catch all. If any exception is raised,
the adapter will ensure that the respective default or a value indicating a
failed operation is returned. This adapter is available for caches as well as
for revalidators.

#### AWS to Laravel Adapter

If you use Laravel with the AWS sdk and you want to enable caching of specific
AWS components by passing Laravel's cache component, you need to ensure that
the given cache is adapted to AWS SDK's needs.

### Extend by Seconds or TTL Fraction

Allows you to wrap a revalidator to extend the given stale cache item by
specific amount of seconds or by a fraction of the original TTL in case the
revalidation returns null (deferred or fault tolerant revalidator). This is
useful when you want to prevent a revalidation spam on a hot key while the item
stays stale.

## What are the requirements?

- PHP 8.2.x or above
- No specific extension required.

## How to install it?

```bash
composer require bitpanda/cache-utils
```

## How to use it?

### Cache Stack

```php
// Imaginary implementation of the PSR-16 interface.
$arrayCache = new ArrayCache(...);

// Imaginary implementation of the PSR-16 interface.
$fileCache = new FileCache(...);

// Imaginary implementation of the PSR-16 interface.
$redisCache = new RedisCache(...);

// PSR-16 implementation that can be used as a drop in replacement for any existing cache.
$cache = new CacheStack([
    $arrayCache,
    $fileCache,
    $redisCache,
]);
```

### Stale-While-Revalidate (SWR)

```php
// Imaginary implementation of the PSR-16 interface.
$arrayCache = new ArrayCache(...);

// Imaginary implementation of the RevalidatorInterface.
$revalidator = new CacheRevalidatorDispatcher(...);

// How long to keep items in cache after they become stale. Null means forever.
$ttlAfterStale = 3600;

// PSR-16 implementation that can be used as a drop in replacement for any existing cache.
$cache = new StaleWhileRevalidateCache($arrayCache, $revalidator, $ttlAfterStale);
```

## How to contribute?

`bitpanda/cache-utils` follows semantic versioning. Read more on
[semver.org][3].

Create issues to report problems or requests. Fork and create pull requests to
propose solutions and ideas. Always add a CHANGELOG.md entry in the unreleased
section.

[1]: https://github.com/bitpanda/cache-utils/actions/workflows/ci.yml/badge.svg
[2]: https://github.com/bitpanda/cache-utils/actions/workflows/ci.yml
[3]: https://semver.org
