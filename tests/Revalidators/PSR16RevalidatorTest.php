<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils\Tests\Revalidators;

use Bitpanda\CacheUtils\CacheItem;
use Bitpanda\CacheUtils\Revalidators\PSR16Revalidator;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class PSR16RevalidatorTest extends TestCase
{
    #[Test]
    public function it_revalidates_multiple_cache_items(): void
    {
        $source = $this->createMock(CacheInterface::class);
        $source
            ->expects($this->once())
            ->method('getMultiple')
            ->with(['key1', 'key2'])
            ->willReturn([
                'key1' => 'value1',
                'key2' => 'value2',
            ]);

        $revalidator = new PSR16Revalidator($source);
        $cache = $this->createMock(CacheInterface::class);
        $staleCacheItems = [
            'key1' => new CacheItem('staleValue1', CarbonImmutable::now()->timestamp - 501, 500),
            'key2' => new CacheItem('staleValue2', CarbonImmutable::now()->timestamp - 501, 500),
        ];

        $cacheItems = $revalidator->revalidateMultiple($cache, $staleCacheItems);

        $this->assertArrayHasKey('key1', $cacheItems);
        $this->assertArrayHasKey('key2', $cacheItems);

        $this->assertInstanceOf(CacheItem::class, $cacheItems['key1']);
        $this->assertInstanceOf(CacheItem::class, $cacheItems['key2']);

        $this->assertSame('value1', $cacheItems['key1']->value);
        $this->assertSame('value2', $cacheItems['key2']->value);
    }

    #[Test]
    public function it_revalidates_multiple_cache_items_with_cache_miss_at_source(): void
    {
        $source = $this->createMock(CacheInterface::class);
        $source
            ->expects($this->once())
            ->method('getMultiple')
            ->with(['key1', 'key2'])
            ->willReturn([
                'key1' => 'value1',
                'key2' => null,
            ]);

        $revalidator = new PSR16Revalidator($source);
        $cache = $this->createMock(CacheInterface::class);
        $staleCacheItems = [
            'key1' => new CacheItem('staleValue1', CarbonImmutable::now()->timestamp - 501, 500),
            'key2' => new CacheItem('staleValue2', CarbonImmutable::now()->timestamp - 501, 500),
        ];

        $cacheItems = $revalidator->revalidateMultiple($cache, $staleCacheItems);

        $this->assertArrayHasKey('key1', $cacheItems);
        $this->assertArrayHasKey('key2', $cacheItems);

        $this->assertInstanceOf(CacheItem::class, $cacheItems['key1']);

        $this->assertSame('value1', $cacheItems['key1']->value);
        $this->assertNull($cacheItems['key2']);
    }

    #[Test]
    public function it_revalidates_single_cache_item(): void
    {
        $source = $this->createMock(CacheInterface::class);
        $source->expects($this->once())->method('get')->with('key')->willReturn('value');

        $revalidator = new PSR16Revalidator($source);
        $cache = $this->createMock(CacheInterface::class);
        $staleCacheItem = new CacheItem('staleValue', CarbonImmutable::now()->timestamp - 501, 500);

        $cacheItem = $revalidator->revalidate($cache, 'key', $staleCacheItem);

        $this->assertInstanceOf(CacheItem::class, $cacheItem);
        $this->assertSame('value', $cacheItem->value);
    }

    #[Test]
    public function it_revalidates_single_cache_item_with_cache_miss_at_source(): void
    {
        $source = $this->createMock(CacheInterface::class);
        $source->expects($this->once())->method('get')->with('key')->willReturn(null);

        $revalidator = new PSR16Revalidator($source);
        $cache = $this->createMock(CacheInterface::class);
        $staleCacheItem = new CacheItem('staleValue', CarbonImmutable::now()->timestamp - 501, 500);

        $cacheItem = $revalidator->revalidate($cache, 'key', $staleCacheItem);

        $this->assertNull($cacheItem);
    }
}
