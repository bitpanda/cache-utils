<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils\Caches\Adapters;

use Psr\SimpleCache\CacheInterface;

class KeySuffix implements CacheInterface
{
    public function __construct(
        protected readonly CacheInterface $cache,
        protected readonly string $suffix,
    ) {
    }

    public function clear(): bool
    {
        return $this->cache->clear();
    }

    public function delete($key): bool
    {
        return $this->cache->delete($this->key($key));
    }

    public function deleteMultiple($keys): bool
    {
        return $this->cache->deleteMultiple($this->keys($keys));
    }

    public function get($key, $default = null): mixed
    {
        return $this->cache->get($this->key($key), $default);
    }

    public function getMultiple($keys, $default = null): iterable
    {
        return $this->cache->getMultiple($this->keys($keys), $default);
    }

    public function has($key): bool
    {
        return $this->cache->has($this->key($key));
    }

    public function set($key, $value, $ttl = null): bool
    {
        return $this->cache->set($this->key($key), $value, $ttl);
    }

    /**
     * @param iterable<string,mixed> $values
     */
    public function setMultiple($values, $ttl = null): bool
    {
        if (!is_array($values)) {
            $values = iterator_to_array($values, true);
        }

        return $this->cache->setMultiple(
            array_combine($this->keys(array_keys($values)), array_values($values)),
            $ttl,
        );
    }

    private function key(string $key): string
    {
        return "{$key}:{$this->suffix}";
    }

    /**
     * @param iterable<string> $keys
     * @return array<int|string,string>
     */
    private function keys(iterable $keys): array
    {
        if (!is_array($keys)) {
            $keys = iterator_to_array($keys, true);
        }

        return array_map(fn (string $key): string => $this->key($key), $keys);
    }
}
