<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils\Tests;

use Bitpanda\CacheUtils\CacheItem;
use Carbon\CarbonImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CacheItemTest extends TestCase
{
    #[Test]
    public function it_does_not_throw_exception_when_extending_by_ttl_fraction_exactly_one(): void
    {
        $cacheItem = new CacheItem('value', CarbonImmutable::now()->timestamp, 500);

        $extendedCacheItem = $cacheItem->extendByTtlFraction(1);

        $this->assertSame($cacheItem->value, $extendedCacheItem->value);
        $this->assertSame($cacheItem->createdAt, $extendedCacheItem->createdAt);
        $this->assertSame($cacheItem->ttl, $extendedCacheItem->ttl);
        $this->assertSame(500, $extendedCacheItem->extensionTtl);
    }

    #[Test]
    public function it_does_not_throw_exception_when_extending_by_ttl_fraction_exactly_zero(): void
    {
        $cacheItem = new CacheItem('value', CarbonImmutable::now()->timestamp, 500);

        $extendedCacheItem = $cacheItem->extendByTtlFraction(0);

        $this->assertSame($cacheItem->value, $extendedCacheItem->value);
        $this->assertSame($cacheItem->createdAt, $extendedCacheItem->createdAt);
        $this->assertSame($cacheItem->ttl, $extendedCacheItem->ttl);
        $this->assertSame(0, $extendedCacheItem->extensionTtl);
    }

    #[Test]
    public function it_extends_by_seconds(): void
    {
        $cacheItem = new CacheItem('value', CarbonImmutable::now()->timestamp, 500);

        $extendedCacheItem = $cacheItem->extendBySeconds(100);

        $this->assertSame($cacheItem->value, $extendedCacheItem->value);
        $this->assertSame($cacheItem->createdAt, $extendedCacheItem->createdAt);
        $this->assertSame($cacheItem->ttl, $extendedCacheItem->ttl);
        $this->assertSame(100, $extendedCacheItem->extensionTtl);

        $furtherExtendedCacheItem = $extendedCacheItem->extendBySeconds(100);

        $this->assertSame($extendedCacheItem->value, $furtherExtendedCacheItem->value);
        $this->assertSame($extendedCacheItem->createdAt, $furtherExtendedCacheItem->createdAt);
        $this->assertSame($extendedCacheItem->ttl, $furtherExtendedCacheItem->ttl);
        $this->assertSame(200, $furtherExtendedCacheItem->extensionTtl);
    }

    #[Test]
    public function it_extends_by_ttl_fraction(): void
    {
        $cacheItem = new CacheItem('value', CarbonImmutable::now()->timestamp, 500);

        $extendedCacheItem = $cacheItem->extendByTtlFraction(0.5);

        $this->assertSame($cacheItem->value, $extendedCacheItem->value);
        $this->assertSame($cacheItem->createdAt, $extendedCacheItem->createdAt);
        $this->assertSame($cacheItem->ttl, $extendedCacheItem->ttl);
        $this->assertSame(250, $extendedCacheItem->extensionTtl);

        $furtherExtendedCacheItem = $extendedCacheItem->extendByTtlFraction(0.5);

        $this->assertSame($extendedCacheItem->value, $furtherExtendedCacheItem->value);
        $this->assertSame($extendedCacheItem->createdAt, $furtherExtendedCacheItem->createdAt);
        $this->assertSame($extendedCacheItem->ttl, $furtherExtendedCacheItem->ttl);
        $this->assertSame(500, $furtherExtendedCacheItem->extensionTtl);
    }

    #[Test]
    public function it_is_not_stale(): void
    {
        $cacheItem = new CacheItem('value', CarbonImmutable::now()->timestamp, 500);

        $this->assertFalse($cacheItem->stale());

        $cacheItem = new CacheItem('value', CarbonImmutable::now()->timestamp, 0);

        $this->assertFalse($cacheItem->stale());

        $cacheItem = new CacheItem('value', CarbonImmutable::now()->timestamp - 1, 0);

        $this->assertTrue($cacheItem->stale());
    }

    #[Test]
    public function it_is_valid_after_stale_extension(): void
    {
        $staleCacheItem = new CacheItem('value', CarbonImmutable::now()->timestamp - 501, 500);

        $this->assertTrue($staleCacheItem->stale());

        $cacheItem = $staleCacheItem->extendBySeconds(1);

        $this->assertFalse($cacheItem->stale());
    }

    #[Test]
    public function it_throws_exception_when_extending_by_invalid_ttl_fraction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Fraction must be between 0 and 1, inclusive. Got: 1.50');

        $cacheItem = new CacheItem('value', CarbonImmutable::now()->timestamp, 500);

        $cacheItem->extendByTtlFraction(1.5);
    }

    #[Test]
    public function it_throws_exception_when_extending_by_negative_ttl_fraction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Fraction must be between 0 and 1, inclusive. Got: -0.50');

        $cacheItem = new CacheItem('value', CarbonImmutable::now()->timestamp, 500);

        $cacheItem->extendByTtlFraction(-0.5);
    }
}
