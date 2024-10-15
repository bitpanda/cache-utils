<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils\Caches;

use Bitpanda\CacheUtils\CacheHelper;
use Bitpanda\CacheUtils\CacheItem;
use Bitpanda\CacheUtils\Exceptions\InvalidStaleWhileRevalidateCacheValueException;
use Bitpanda\CacheUtils\Revalidators\RevalidatorInterface;
use Carbon\CarbonImmutable;
use DateInterval;
use Psr\SimpleCache\CacheInterface;
use UnexpectedValueException;

class StaleWhileRevalidate implements CacheInterface
{
    public function __construct(
        protected readonly CacheInterface $cache,
        protected readonly RevalidatorInterface $revalidator,
        public readonly ?int $ttlAfterStale = null,
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

    /**
     * @param iterable<int,string> $keys
     */
    public function deleteMultiple($keys): bool
    {
        return $this->cache->deleteMultiple($keys);
    }

    public function get($key, $default = null): mixed
    {
        $cacheItem = $this->cache->get($key);
        if ($cacheItem === null) {
            return $default;
        }

        $cacheItem = $this->validateCacheItem($cacheItem, $key);

        if ($cacheItem->stale()) {
            $freshCacheItem = $this->revalidator->revalidate($this->cache, $key, $cacheItem);

            if ($freshCacheItem !== null) {
                $this->setCacheItem($key, $freshCacheItem);

                return $freshCacheItem->value;
            }
        }

        return $cacheItem->value;
    }

    /**
     * @param iterable<int,string> $keys
     * @return iterable<string,mixed>
     */
    public function getMultiple($keys, $default = null): iterable
    {
        /** @var array<string,CacheItem|null> $cacheItems */
        $cacheItems = iterator_to_array($this->cache->getMultiple($keys), true);

        $toRevalidateAndSet = [];
        $maxTtl = 0;

        foreach ($cacheItems as $key => $cacheItem) {
            if ($cacheItem === null) {
                continue;
            }

            $cacheItem = $this->validateCacheItem($cacheItem, $key);
            if ($cacheItem->stale()) {
                $toRevalidateAndSet[$key] = $cacheItem;
                $maxTtl = (int)max($maxTtl, $cacheItem->ttl);

                continue;
            }
        }

        if (!empty($toRevalidateAndSet)) {
            /** @var array<string,CacheItem|null> $newCacheItems */
            $newCacheItems = iterator_to_array($this->revalidator->revalidateMultiple($this->cache, $toRevalidateAndSet), true);

            // Remove not revalidated values
            /** @var array<string,CacheItem> $newCacheItems */
            $newCacheItems = array_filter($newCacheItems, fn (mixed $value): bool => $value !== null);

            if (!empty($newCacheItems)) {
                $this->setMultipleCacheItems($newCacheItems, $maxTtl);
            }

            $cacheItems = array_merge($cacheItems, $newCacheItems);
        }

        return array_map(fn (?CacheItem $cacheItem): mixed => $cacheItem->value ?? $default, $cacheItems);
    }

    public function has($key): bool
    {
        return $this->cache->has($key);
    }

    /**
     * @param null|int|DateInterval $ttl
     */
    public function set($key, $value, $ttl = null): bool
    {
        $ttl = $this->validateTtl($ttl);

        return $this->setCacheItem(
            $key,
            new CacheItem($value, (int)CarbonImmutable::now()->timestamp, $ttl),
        );
    }

    /**
     * @param iterable<string,mixed> $values
     * @param null|int|DateInterval $ttl
     */
    public function setMultiple($values, $ttl = null): bool
    {
        $ttl = $this->validateTtl($ttl);

        $now = (int)CarbonImmutable::now()->timestamp;

        $values = array_map(
            fn (mixed $value): CacheItem => new CacheItem($value, $now, $ttl),
            iterator_to_array($values, true),
        );

        return $this->setMultipleCacheItems($values, $ttl);
    }

    protected function setCacheItem(string $key, CacheItem $cacheItem): bool
    {
        return $this->cache->set(
            $key,
            $cacheItem,
            $this->ttlAfterStale === null ? null : $cacheItem->ttl + $this->ttlAfterStale,
        );
    }

    /**
     * @param iterable<string,CacheItem> $cacheItems
     */
    protected function setMultipleCacheItems(iterable $cacheItems, int $ttl): bool
    {
        $x = $this->cache->setMultiple(
            $cacheItems,
            $this->ttlAfterStale === null ? null : $ttl + $this->ttlAfterStale,
        );

        return $x;
    }

    private function validateCacheItem(mixed $item, string $key): CacheItem
    {
        if (!($item instanceof CacheItem)) {
            throw new InvalidStaleWhileRevalidateCacheValueException(sprintf(
                'Value for key %s of type %s is not an instance of %s',
                $key,
                gettype($item),
                CacheItem::class,
            ));
        }

        return $item;
    }

    /**
     * @param null|int|DateInterval $ttl
     */
    private function validateTtl($ttl): int
    {
        if ($ttl instanceof DateInterval) {
            $ttl = CacheHelper::dateIntervalToSeconds($ttl);
        }

        if (!is_int($ttl) || $ttl < 0) {
            throw new UnexpectedValueException(sprintf(
                'A ttl for stale while revalidate caches must be provided ' .
                'and be a positive integer, %s (%s) given.',
                gettype($ttl),
                var_export($ttl, true),
            ));
        }

        return $ttl;
    }
}
