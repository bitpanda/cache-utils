<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils\Revalidators\Adapters;

use Bitpanda\CacheUtils\CacheItem;
use Bitpanda\CacheUtils\Revalidators\RevalidatorInterface;

class ExtendBySeconds extends AbstractExtendBy
{
    public function __construct(
        RevalidatorInterface $revalidator,
        public readonly int $extensionSeconds,
    ) {
        parent::__construct($revalidator);
    }

    protected function extend(CacheItem $staleCacheItem): CacheItem
    {
        return $staleCacheItem->extendBySeconds($this->extensionSeconds);
    }
}
