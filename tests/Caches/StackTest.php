<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils\Tests\Caches;

use Bitpanda\CacheUtils\Caches\Stack;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class StackTest extends TestCase
{
    public function test_cache_get(): void
    {
        $firstCache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $firstCache
            ->method('get')
            ->withAnyParameters()
            ->willReturn(null);
        $secondCache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $secondCache
            ->method('get')
            ->withAnyParameters()
            ->willReturn('abc');
        $cacheStack = new Stack([$firstCache, $secondCache]);
        $this->assertSame($cacheStack->get('any-key'), 'abc');
    }
}
