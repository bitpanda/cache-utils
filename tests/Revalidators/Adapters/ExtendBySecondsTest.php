<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils\Tests\Revalidators\Adapters;

use Bitpanda\CacheUtils\CacheItem;
use Bitpanda\CacheUtils\Revalidators\Adapters\ExtendBySeconds;
use Bitpanda\CacheUtils\Revalidators\RevalidatorInterface;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class ExtendBySecondsTest extends TestCase
{
    #[Test]
    public function it_extends_item_on_stale_revalidation(): void
    {
        /** @var RevalidatorInterface&MockObject $revalidator */
        $revalidator = $this->createMock(RevalidatorInterface::class);
        /** @var CacheInterface&MockObject $cache */
        $cache = $this->createMock(CacheInterface::class);
        $staleCacheItem = new CacheItem('value', CarbonImmutable::now()->timestamp - 501, 500);

        $revalidator
            ->expects(self::once())
            ->method('revalidate')
            ->with($cache, 'key', $staleCacheItem)
            ->willReturn(null);

        $extendingRevalidator = new ExtendBySeconds($revalidator, 500);
        $result = $extendingRevalidator->revalidate($cache, 'key', $staleCacheItem);

        $this->assertInstanceOf(CacheItem::class, $result);
        $this->assertSame('value', $result->value);
        $this->assertSame($staleCacheItem->createdAt, $result->createdAt);
        $this->assertSame($staleCacheItem->ttl, $result->ttl);
        $this->assertSame(0, $staleCacheItem->extensionTtl);
        $this->assertSame(500, $result->extensionTtl);
        $this->assertTrue($staleCacheItem->stale());
        $this->assertFalse($result->stale());
    }

    #[Test]
    public function it_extends_items_on_stale_revalidation(): void
    {
        /** @var RevalidatorInterface&MockObject $revalidator */
        $revalidator = $this->createMock(RevalidatorInterface::class);
        /** @var CacheInterface&MockObject $cache */
        $cache = $this->createMock(CacheInterface::class);
        $staleCacheItems = [
            'key1' => new CacheItem('value1', CarbonImmutable::now()->timestamp - 501, 500),
            'key2' => new CacheItem('value2', CarbonImmutable::now()->timestamp - 501, 500),
        ];

        $revalidator
            ->expects(self::once())
            ->method('revalidateMultiple')
            ->with($cache, $staleCacheItems)
            ->willReturn([
                'key1' => new CacheItem('value1_new', CarbonImmutable::now()->timestamp, 500),
                'key2' => null,
            ]);

        $extendingRevalidator = new ExtendBySeconds($revalidator, 500);
        $result = $extendingRevalidator->revalidateMultiple($cache, $staleCacheItems);

        $this->assertCount(2, $result);
        $this->assertInstanceOf(CacheItem::class, $result['key1']);
        $this->assertSame('value1_new', $result['key1']->value);
        $this->assertNotSame($staleCacheItems['key1']->createdAt, $result['key1']->createdAt);
        $this->assertSame($staleCacheItems['key1']->ttl, $result['key1']->ttl);
        $this->assertSame(0, $staleCacheItems['key1']->extensionTtl);
        $this->assertSame(0, $result['key1']->extensionTtl);
        $this->assertTrue($staleCacheItems['key1']->stale());
        $this->assertFalse($result['key1']->stale());

        $this->assertInstanceOf(CacheItem::class, $result['key2']);
        $this->assertSame('value2', $result['key2']->value);
        $this->assertSame($staleCacheItems['key2']->createdAt, $result['key2']->createdAt);
        $this->assertSame($staleCacheItems['key2']->ttl, $result['key2']->ttl);
        $this->assertSame(0, $staleCacheItems['key2']->extensionTtl);
        $this->assertSame(500, $result['key2']->extensionTtl);
        $this->assertTrue($staleCacheItems['key2']->stale());
        $this->assertFalse($result['key2']->stale());
    }
}
