<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils\Revalidators\Adapters;

use Bitpanda\CacheUtils\CacheItem;
use Bitpanda\CacheUtils\Revalidators\RevalidatorInterface;

class ExtendByTtlFraction extends AbstractExtendBy
{
    public function __construct(
        RevalidatorInterface $revalidator,
        public readonly float $extensionTtlFraction,
    ) {
        parent::__construct($revalidator);
    }

    protected function extend(CacheItem $staleCacheItem): CacheItem
    {
        return $staleCacheItem->extendByTtlFraction($this->extensionTtlFraction);
    }
}
