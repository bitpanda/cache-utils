<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils\Revalidators;

use Bitpanda\CacheUtils\CacheItem;
use Carbon\CarbonImmutable;
use Psr\SimpleCache\CacheInterface;

class PSR16Revalidator implements RevalidatorInterface
{
    public function __construct(protected readonly CacheInterface $source)
    {
    }

    public function revalidate(CacheInterface $cache, string $key, CacheItem $staleCacheItem): ?CacheItem
    {
        $sourceValue = $this->source->get($key);
        if ($sourceValue === null) {
            return null;
        }

        return new CacheItem($sourceValue, (int)CarbonImmutable::now()->timestamp, $staleCacheItem->ttl);
    }

    public function revalidateMultiple(CacheInterface $cache, iterable $staleCacheItems): iterable
    {
        /** @var array<string,CacheItem> $staleCacheArray */
        $staleCacheArray = iterator_to_array($staleCacheItems, true);
        $sourceValues = $this->source->getMultiple(array_keys($staleCacheArray));

        foreach ($sourceValues as $key => &$sourceValue) {
            if ($sourceValue === null) {
                continue;
            }

            $sourceValue = new CacheItem($sourceValue, (int)CarbonImmutable::now()->timestamp, $staleCacheArray[$key]->ttl);
        }

        /** @var array<string,CacheItem|null> */
        return $sourceValues;
    }
}
