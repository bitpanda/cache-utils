<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils\Revalidators\Adapters;

use Bitpanda\CacheUtils\CacheItem;
use Bitpanda\CacheUtils\Revalidators\RevalidatorInterface;
use Psr\SimpleCache\CacheInterface;
use Throwable;

class FaultTolerancy implements RevalidatorInterface
{
    public function __construct(protected readonly RevalidatorInterface $revalidator)
    {
    }

    public function revalidate(CacheInterface $cache, string $key, CacheItem $staleCacheItem): ?CacheItem
    {
        try {
            return $this->revalidator->revalidate($cache, $key, $staleCacheItem);
        } catch (Throwable) {
            return null;
        }
    }

    public function revalidateMultiple(CacheInterface $cache, iterable $staleCacheItems): iterable
    {
        try {
            return $this->revalidator->revalidateMultiple($cache, $staleCacheItems);
        } catch (Throwable) {
            return array_map(
                fn (): null => null,
                iterator_to_array($staleCacheItems, true),
            );
        }
    }
}
