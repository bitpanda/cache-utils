<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils\Caches\Adapters;

use Psr\SimpleCache\CacheInterface;
use Throwable;

class FaultTolerancy implements CacheInterface
{
    public function __construct(protected readonly CacheInterface $cache)
    {
    }

    public function clear(): bool
    {
        try {
            return $this->cache->clear();
        } catch (Throwable) {
            return false;
        }
    }

    public function delete($key): bool
    {
        try {
            return $this->cache->delete($key);
        } catch (Throwable) {
            return false;
        }
    }

    public function deleteMultiple($keys): bool
    {
        try {
            return $this->cache->deleteMultiple($keys);
        } catch (Throwable) {
            return false;
        }
    }

    public function get($key, $default = null): mixed
    {
        try {
            return $this->cache->get($key, $default);
        } catch (Throwable) {
            return $default;
        }
    }

    public function getMultiple($keys, $default = null): iterable
    {
        try {
            return $this->cache->getMultiple($keys, $default);
        } catch (Throwable) {
            return array_fill_keys(iterator_to_array($keys, true), $default);
        }
    }

    public function has($key): bool
    {
        try {
            return $this->cache->has($key);
        } catch (Throwable) {
            return false;
        }
    }

    public function set($key, $value, $ttl = null): bool
    {
        try {
            return $this->cache->set($key, $value, $ttl);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param iterable<string,mixed> $values
     */
    public function setMultiple($values, $ttl = null): bool
    {
        try {
            return $this->cache->setMultiple($values, $ttl);
        } catch (Throwable) {
            return false;
        }
    }
}
