<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils\Revalidators\Adapters;

use Bitpanda\CacheUtils\CacheItem;
use Bitpanda\CacheUtils\Revalidators\RevalidatorInterface;
use Psr\SimpleCache\CacheInterface;

abstract class AbstractExtendBy implements RevalidatorInterface
{
    public function __construct(
        protected readonly RevalidatorInterface $revalidator,
    ) {
    }

    public function revalidate(CacheInterface $cache, string $key, CacheItem $staleCacheItem): ?CacheItem
    {
        $cacheItem = $this->revalidator->revalidate($cache, $key, $staleCacheItem);
        if ($cacheItem === null) {
            return $this->extend($staleCacheItem);
        }

        return $cacheItem;
    }

    public function revalidateMultiple(CacheInterface $cache, iterable $staleCacheItems): iterable
    {
        $cacheItems = iterator_to_array($this->revalidator->revalidateMultiple($cache, $staleCacheItems), true);

        foreach ($staleCacheItems as $key => $staleCacheItem) {
            if (!isset($cacheItems[$key])) {
                $cacheItems[$key] = $this->extend($staleCacheItem);
            }
        }

        return $cacheItems;
    }

    abstract protected function extend(CacheItem $staleCacheItem): CacheItem;
}
