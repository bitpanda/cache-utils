<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils\Tests\Caches\Adapters;

use Bitpanda\CacheUtils\Caches\Adapters\FaultTolerancy;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;

class FaultTolerancyTest extends TestCase
{
    #[Test]
    public function it_doed_not_fail_on_clear(): void
    {
        /** @var CacheInterface&MockObject $innerCache */
        $innerCache = $this->createMock(CacheInterface::class);
        $innerCache->expects($this->once())
            ->method('clear')
            ->willThrowException(new RuntimeException());

        $cache = new FaultTolerancy($innerCache);
        $result = $cache->clear();

        $this->assertFalse($result);
    }

    #[Test]
    public function it_doed_not_fail_on_delete(): void
    {
        /** @var CacheInterface&MockObject $innerCache */
        $innerCache = $this->createMock(CacheInterface::class);
        $innerCache->expects($this->once())
            ->method('delete')
            ->with('key')
            ->willThrowException(new RuntimeException());

        $cache = new FaultTolerancy($innerCache);
        $result = $cache->delete('key');

        $this->assertFalse($result);
    }

    #[Test]
    public function it_doed_not_fail_on_delete_multiple(): void
    {
        /** @var CacheInterface&MockObject $innerCache */
        $innerCache = $this->createMock(CacheInterface::class);
        $innerCache->expects($this->once())
            ->method('deleteMultiple')
            ->with(['key'])
            ->willThrowException(new RuntimeException());

        $cache = new FaultTolerancy($innerCache);
        $result = $cache->deleteMultiple(['key']);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_doed_not_fail_on_get(): void
    {
        /** @var CacheInterface&MockObject $innerCache */
        $innerCache = $this->createMock(CacheInterface::class);
        $innerCache->expects($this->once())
            ->method('get')
            ->with('key')
            ->willThrowException(new RuntimeException());

        $cache = new FaultTolerancy($innerCache);
        $result = $cache->get('key');

        $this->assertNull($result);
    }

    #[Test]
    public function it_doed_not_fail_on_get_multiple(): void
    {
        /** @var CacheInterface&MockObject $innerCache */
        $innerCache = $this->createMock(CacheInterface::class);
        $innerCache->expects($this->once())
            ->method('getMultiple')
            ->with(['key1', 'key2'])
            ->willThrowException(new RuntimeException());

        $cache = new FaultTolerancy($innerCache);
        $result = $cache->getMultiple(['key1', 'key2']);

        $this->assertSame(['key1' => null, 'key2' => null], $result);
    }

    #[Test]
    public function it_doed_not_fail_on_has(): void
    {
        /** @var CacheInterface&MockObject $innerCache */
        $innerCache = $this->createMock(CacheInterface::class);
        $innerCache->expects($this->once())
            ->method('has')
            ->with('key')
            ->willThrowException(new RuntimeException());

        $cache = new FaultTolerancy($innerCache);
        $result = $cache->has('key');

        $this->assertFalse($result);
    }

    #[Test]
    public function it_doed_not_fail_on_set(): void
    {
        /** @var CacheInterface&MockObject $innerCache */
        $innerCache = $this->createMock(CacheInterface::class);
        $innerCache->expects($this->once())
            ->method('set')
            ->with('key', 'value', 60)
            ->willThrowException(new RuntimeException());

        $cache = new FaultTolerancy($innerCache);
        $result = $cache->set('key', 'value', 60);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_doed_not_fail_on_set_multiple(): void
    {
        /** @var CacheInterface&MockObject $innerCache */
        $innerCache = $this->createMock(CacheInterface::class);
        $innerCache->expects($this->once())
            ->method('setMultiple')
            ->with(['key1' => 'value1', 'key2' => 'value2'], 60)
            ->willThrowException(new RuntimeException());

        $cache = new FaultTolerancy($innerCache);
        $result = $cache->setMultiple(['key1' => 'value1', 'key2' => 'value2'], 60);

        $this->assertFalse($result);
    }
}
