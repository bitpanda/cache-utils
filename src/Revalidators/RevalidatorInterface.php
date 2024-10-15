<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils\Revalidators;

use Bitpanda\CacheUtils\CacheItem;
use Psr\SimpleCache\CacheInterface;

interface RevalidatorInterface
{
    /**
     * Must return null if the value is either not available (e.g. fault
     * tolerant revalidator) or when the revalidation can not happen
     * synchronously.
     *
     * Provided cache item is expected to be stale.
     */
    public function revalidate(
        CacheInterface $cache,
        string $key,
        CacheItem $staleCacheItem,
    ): ?CacheItem;

    /**
     * The returned set must contain null values if the value is either not
     * available (e.g. fault tolerant revalidator) or when the revalidation can
     * not happen synchronously. Implies that the same amount of elements must
     * be returned as provided.
     *
     * Provided cache items are expected to be stale.
     *
     * @param iterable<string,CacheItem> $staleCacheItems
     * @return iterable<string,CacheItem|null>
     */
    public function revalidateMultiple(
        CacheInterface $cache,
        iterable $staleCacheItems,
    ): iterable;
}
