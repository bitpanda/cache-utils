<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

readonly class CacheItem
{
    public function __construct(
        public mixed $value,
        public int $createdAt,
        public int $ttl,
        public int $extensionTtl = 0,
    ) {
    }

    public function extendBySeconds(int $seconds): self
    {
        return new self($this->value, $this->createdAt, $this->ttl, $this->extensionTtl + $seconds);
    }

    public function extendByTtlFraction(float $fraction): self
    {
        if ($fraction < 0 || $fraction > 1) {
            throw new InvalidArgumentException(sprintf(
                'Fraction must be between 0 and 1, inclusive. Got: %.2F',
                $fraction,
            ));
        }

        return $this->extendBySeconds((int)($this->ttl * $fraction));
    }

    public function stale(): bool
    {
        return $this->createdAt + $this->ttl + $this->extensionTtl < CarbonImmutable::now()->timestamp;
    }
}
