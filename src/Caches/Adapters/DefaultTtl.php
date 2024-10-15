<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils\Caches\Adapters;

use DateInterval;
use Psr\SimpleCache\CacheInterface;

class DefaultTtl implements CacheInterface
{
    public function __construct(
        protected readonly CacheInterface $cache,
        protected readonly null|int|DateInterval $ttl = null,
    ) {
    }

    public function clear(): bool
    {
        return $this->cache->clear();
    }

    public function delete($key): bool
    {
        return $this->cache->delete($key);
    }

    public function deleteMultiple($keys): bool
    {
        return $this->cache->deleteMultiple($keys);
    }

    public function get($key, $default = null): mixed
    {
        return $this->cache->get($key, $default);
    }

    public function getMultiple($keys, $default = null): iterable
    {
        return $this->cache->getMultiple($keys, $default);
    }

    public function has($key): bool
    {
        return $this->cache->has($key);
    }

    public function set($key, $value, $ttl = null): bool
    {
        return $this->cache->set($key, $value, $ttl ?? $this->ttl);
    }

    /**
     * @param iterable<string,mixed> $values
     */
    public function setMultiple($values, $ttl = null): bool
    {
        return $this->cache->setMultiple($values, $ttl ?? $this->ttl);
    }
}
