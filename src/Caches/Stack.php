<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils\Caches;

use DateInterval;
use Psr\SimpleCache\CacheInterface;

class Stack implements CacheInterface
{
    /**
     * @param array<int,\Psr\SimpleCache\CacheInterface> $caches
     */
    public function __construct(protected readonly array $caches)
    {
    }

    public function clear(): bool
    {
        $result = true;

        /** @var CacheInterface $cache */
        foreach ($this->caches as $cache) {
            if (!$cache->clear()) {
                $result = false;
            }
        }

        return $result;
    }

    public function delete($key): bool
    {
        $result = true;

        /** @var CacheInterface $cache */
        foreach ($this->caches as $cache) {
            if (!$cache->delete($key)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @param iterable<int,string> $keys
     */
    public function deleteMultiple($keys): bool
    {
        $result = true;

        /** @var CacheInterface $cache */
        foreach ($this->caches as $cache) {
            if (!$cache->deleteMultiple($keys)) {
                $result = false;
            }
        }

        return $result;
    }

    public function get($key, $default = null): mixed
    {
        $value = $default;

        /** @var array<int,\Psr\SimpleCache\CacheInterface> $misses */
        $misses = [];

        /** @var CacheInterface $cache */
        foreach ($this->caches as $cache) {
            $value = $cache->get($key);

            if ($value !== null) {
                break;
            }

            $misses[] = $cache;
        }

        if ($value !== null) {
            /** @var CacheInterface $cache */
            foreach ($misses as $cache) {
                $cache->set($key, $value);
            }
        }

        return $value ?? $default;
    }

    /**
     * @param iterable<int,string> $keys
     * @return iterable<string,mixed>
     */
    public function getMultiple($keys, $default = null): iterable
    {
        $values = [];
        $hit = [];
        /** @var array<int,\Psr\SimpleCache\CacheInterface> $misses */
        $misses = [];

        /** @var CacheInterface $cache */
        foreach ($this->caches as $cache) {
            $values = $cache->getMultiple($keys);
            if (!is_array($values)) {
                $values = iterator_to_array($values, true);
            }
            $hit = array_filter($values, fn ($e) => $e !== null);

            if (count($hit) === count($values)) {
                break;
            }

            $misses[] = $cache;
        }

        if (count($hit) !== count($values)) {
            /** @var CacheInterface $cache */
            foreach ($misses as $cache) {
                $cache->setMultiple($hit);
            }
        }

        return array_map(fn ($e) => $e ?? $default, $values);
    }

    public function has($key): bool
    {
        /** @var CacheInterface $cache */
        foreach ($this->caches as $cache) {
            if ($cache->has($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param null|int|DateInterval $ttl
     */
    public function set($key, $value, $ttl = null): bool
    {
        $result = true;

        /** @var CacheInterface $cache */
        foreach ($this->caches as $cache) {
            if (!$cache->set($key, $value, $ttl)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @param iterable<string,mixed> $values
     */
    public function setMultiple($values, $ttl = null): bool
    {
        $result = true;

        /** @var CacheInterface $cache */
        foreach ($this->caches as $cache) {
            if (!$cache->setMultiple($values, $ttl)) {
                $result = false;
            }
        }

        return $result;
    }
}
