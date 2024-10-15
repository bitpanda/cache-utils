<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils\Tests\Revalidators\Adapters;

use Bitpanda\CacheUtils\CacheItem;
use Bitpanda\CacheUtils\Revalidators\Adapters\FaultTolerancy;
use Bitpanda\CacheUtils\Revalidators\RevalidatorInterface;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;

class FaultTolerancyTest extends TestCase
{
    #[Test]
    public function it_revalidates_a_single_cache_item_without_failure(): void
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
            ->willThrowException(new RuntimeException());

        $faultTolerantRevalidator = new FaultTolerancy($revalidator);
        $result = $faultTolerantRevalidator->revalidate($cache, 'key', $staleCacheItem);

        $this->assertNull($result);
    }

    #[Test]
    public function it_revalidates_many_cache_items_without_failure(): void
    {
        /** @var RevalidatorInterface&MockObject $revalidator */
        $revalidator = $this->createMock(RevalidatorInterface::class);
        /** @var CacheInterface&MockObject $cache */
        $cache = $this->createMock(CacheInterface::class);
        $staleCacheItem = new CacheItem('value', CarbonImmutable::now()->timestamp - 501, 500);

        $revalidator
            ->expects(self::once())
            ->method('revalidateMultiple')
            ->with($cache, ['key' => $staleCacheItem])
            ->willThrowException(new RuntimeException());

        $faultTolerantRevalidator = new FaultTolerancy($revalidator);
        $result = $faultTolerantRevalidator->revalidateMultiple($cache, ['key' => $staleCacheItem]);

        $this->assertSame(['key' => null], $result);
    }
}
