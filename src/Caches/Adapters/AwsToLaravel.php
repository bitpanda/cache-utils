<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils\Caches\Adapters;

use Aws\Psr16CacheAdapter;

class AwsToLaravel extends Psr16CacheAdapter
{
    /** @phpstan-ignore-next-line */
    public function set($key, $value, $ttl = 0)
    {
        $parsedTtl = $ttl === 0 ? null : $ttl;

        /** @phpstan-ignore-next-line */
        return parent::set($key, $value, $parsedTtl);
    }
}
