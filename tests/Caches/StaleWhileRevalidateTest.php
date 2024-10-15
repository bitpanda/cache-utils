<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils\Tests\Caches;

use Bitpanda\CacheUtils\CacheItem;
use Bitpanda\CacheUtils\Caches\StaleWhileRevalidate;
use Bitpanda\CacheUtils\Exceptions\InvalidStaleWhileRevalidateCacheValueException;
use Bitpanda\CacheUtils\Revalidators\PSR16Revalidator;
use Bitpanda\CacheUtils\Revalidators\RevalidatorInterface;
use DateInterval;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use UnexpectedValueException;

class StaleWhileRevalidateTest extends TestCase
{
    #[Test]
    public function it_checks_if_a_cache_item_exists(): void
    {
        $key = 'test-key';

        /** @var CacheInterface&MockObject $cache */
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $cache
            ->expects($this->once())
            ->method('has')
            ->with($key)
            ->willReturn(true);

        /** @var RevalidatorInterface&MockObject $revalidator */
        $revalidator = $this->getMockBuilder(RevalidatorInterface::class)->getMock();

        $sWRCache = new StaleWhileRevalidate($cache, $revalidator);
        $this->assertTrue($sWRCache->has($key));
    }

    #[Test]
    public function it_clears_the_cache(): void
    {
        /** @var CacheInterface&MockObject $cache */
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $cache
            ->expects($this->once())
            ->method('clear')
            ->willReturn(true);

        /** @var RevalidatorInterface&MockObject $revalidator */
        $revalidator = $this->getMockBuilder(RevalidatorInterface::class)->getMock();

        $sWRCache = new StaleWhileRevalidate($cache, $revalidator);
        $this->assertTrue($sWRCache->clear());
    }

    #[Test]
    public function it_deletes_a_cache_item(): void
    {
        $key = 'test-key';

        /** @var CacheInterface&MockObject $cache */
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $cache
            ->expects($this->once())
            ->method('delete')
            ->with($key)
            ->willReturn(true);

        /** @var RevalidatorInterface&MockObject $revalidator */
        $revalidator = $this->getMockBuilder(RevalidatorInterface::class)->getMock();

        $sWRCache = new StaleWhileRevalidate($cache, $revalidator);
        $this->assertTrue($sWRCache->delete($key));
    }

    #[Test]
    public function it_deletes_multiple_cache_items(): void
    {
        $keys = ['key1', 'key2'];

        /** @var CacheInterface&MockObject $cache */
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $cache
            ->expects($this->once())
            ->method('deleteMultiple')
            ->with($keys)
            ->willReturn(true);

        /** @var RevalidatorInterface&MockObject $revalidator */
        $revalidator = $this->getMockBuilder(RevalidatorInterface::class)->getMock();

        $sWRCache = new StaleWhileRevalidate($cache, $revalidator);
        $this->assertTrue($sWRCache->deleteMultiple($keys));
    }

    #[Test]
    public function it_returns_default_value_on_cache_miss(): void
    {
        /** @var CacheInterface&MockObject $cache */
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $cache
            ->expects($this->once())
            ->method('get')
            ->with('key')
            ->willReturn(null);

        /** @var RevalidatorInterface&MockObject $revalidator */
        $revalidator = $this->getMockBuilder(RevalidatorInterface::class)->getMock();
        $revalidator
            ->expects($this->never())
            ->method('revalidate');

        $sWRCache = new StaleWhileRevalidate($cache, $revalidator);

        $this->assertSame('default-value', $sWRCache->get('key', 'default-value'));
    }

    #[Test]
    public function it_returns_default_value_on_cache_miss_using_multiple(): void
    {
        /** @var CacheInterface&MockObject $cache */
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $cache
            ->expects($this->once())
            ->method('getMultiple')
            ->with(['key1', 'key2', 'key3'])
            ->willReturn([
                'key1' => new CacheItem('some-value', time(), 500),
                'key2' => null,
                'key3' => new CacheItem('some-value2', time(), 500),
            ]);

        /** @var RevalidatorInterface&MockObject $revalidator */
        $revalidator = $this->getMockBuilder(RevalidatorInterface::class)->getMock();
        $revalidator
            ->expects($this->never())
            ->method('revalidate');
        $revalidator
            ->expects($this->never())
            ->method('revalidateMultiple');

        $sWRCache = new StaleWhileRevalidate($cache, $revalidator);

        $this->assertSame([
            'key1' => 'some-value',
            'key2' => 'default-value',
            'key3' => 'some-value2',
        ], $sWRCache->getMultiple(['key1', 'key2', 'key3'], 'default-value'));
    }

    #[Test]
    public function it_returns_null_on_cache_miss(): void
    {
        /** @var CacheInterface&MockObject $cache */
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $cache
            ->expects($this->once())
            ->method('get')
            ->with('key')
            ->willReturn(null);

        /** @var RevalidatorInterface&MockObject $revalidator */
        $revalidator = $this->getMockBuilder(RevalidatorInterface::class)->getMock();
        $revalidator
            ->expects($this->never())
            ->method('revalidate');

        $sWRCache = new StaleWhileRevalidate($cache, $revalidator);

        $this->assertNull($sWRCache->get('key'));
    }

    #[Test]
    public function it_returns_null_values_on_cache_miss_using_multiple(): void
    {
        /** @var CacheInterface&MockObject $cache */
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $cache
            ->expects($this->once())
            ->method('getMultiple')
            ->with(['key1', 'key2', 'key3'])
            ->willReturn([
                'key1' => new CacheItem('some-value', time(), 500),
                'key2' => null,
                'key3' => new CacheItem('some-value2', time(), 500),
            ]);

        /** @var RevalidatorInterface&MockObject $revalidator */
        $revalidator = $this->getMockBuilder(RevalidatorInterface::class)->getMock();
        $revalidator
            ->expects($this->never())
            ->method('revalidate');
        $revalidator
            ->expects($this->never())
            ->method('revalidateMultiple');

        $sWRCache = new StaleWhileRevalidate($cache, $revalidator);

        $this->assertSame([
            'key1' => 'some-value',
            'key2' => null,
            'key3' => 'some-value2',
        ], $sWRCache->getMultiple(['key1', 'key2', 'key3']));
    }

    #[Test]
    public function it_returns_still_valid_cache_without_revalidation(): void
    {
        /** @var CacheInterface&MockObject $cache */
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $cache
            ->expects($this->once())
            ->method('get')
            ->with('key')
            ->willReturn(new CacheItem('some-value', time(), 500));

        /** @var RevalidatorInterface&MockObject $revalidator */
        $revalidator = $this->getMockBuilder(RevalidatorInterface::class)->getMock();
        $revalidator
            ->expects($this->never())
            ->method('revalidate');

        $sWRCache = new StaleWhileRevalidate($cache, $revalidator);

        $this->assertSame('some-value', $sWRCache->get('key'));
    }

    #[Test]
    public function it_revalidates_some_keys(): void
    {
        $expiredItem = new CacheItem('some-value', time() - 501, 500);
        $validItem = new CacheItem('some-value2', time(), 500);
        $newItem = new CacheItem('new-value', time(), 500);

        /** @var CacheInterface&MockObject $cache */
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $cache
            ->expects($this->once())
            ->method('getMultiple')
            ->with(['key1', 'key2', 'key3'])
            ->willReturn([
                'key1' => $expiredItem,
                'key2' => null,
                'key3' => $validItem,
            ]);
        $cache
            ->expects($this->once())
            ->method('setMultiple')
            ->with(['key1' => new CacheItem('new-value', time(), 500)])
            ->willReturn(true);

        /** @var RevalidatorInterface&MockObject $revalidator */
        $revalidator = $this->getMockBuilder(RevalidatorInterface::class)->getMock();
        $revalidator
            ->expects($this->never())
            ->method('revalidate');
        $revalidator
            ->expects($this->once())
            ->method('revalidateMultiple')
            ->with($cache, ['key1' => $expiredItem])
            ->willReturn(['key1' => $newItem]);

        $sWRCache = new StaleWhileRevalidate($cache, $revalidator);

        $this->assertSame([
            'key1' => 'new-value',
            'key2' => null,
            'key3' => 'some-value2',
        ], $sWRCache->getMultiple(['key1', 'key2', 'key3']));
    }

    #[Test]
    public function it_revalidates_stale_cache_and_returns_new_value(): void
    {
        $expired = new CacheItem('some-value', time() - 501, 500);
        $new = new CacheItem('new-value', time(), 500);

        /** @var CacheInterface&MockObject $cache */
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $cache
            ->expects($this->once())
            ->method('get')
            ->with('key')
            ->willReturn($expired);
        $cache
            ->expects($this->once())
            ->method('set')
            ->with('key', $new, null)
            ->willReturn(true);

        /** @var RevalidatorInterface&MockObject $revalidator */
        $revalidator = $this->getMockBuilder(RevalidatorInterface::class)->getMock();
        $revalidator
            ->expects($this->once())
            ->method('revalidate')
            ->with($cache, 'key', $expired)
            ->willReturn($new);

        $sWRCache = new StaleWhileRevalidate($cache, $revalidator);

        $this->assertSame('new-value', $sWRCache->get('key'));
    }

    #[Test]
    public function it_revalidates_stale_cache_and_returns_old_value_on_deferred_cache_refresh(): void
    {
        $expired = new CacheItem('some-value', time() - 501, 500);

        /** @var CacheInterface&MockObject $cache */
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $cache
            ->expects($this->once())
            ->method('get')
            ->with('key')
            ->willReturn($expired);
        $cache
            ->expects($this->never())
            ->method('set');

        /** @var RevalidatorInterface&MockObject $revalidator */
        $revalidator = $this->getMockBuilder(RevalidatorInterface::class)->getMock();
        $revalidator
            ->expects($this->once())
            ->method('revalidate')
            ->with($cache, 'key', $expired)
            ->willReturn(null);

        $sWRCache = new StaleWhileRevalidate($cache, $revalidator);

        $this->assertSame('some-value', $sWRCache->get('key'));
    }

    #[Test]
    public function it_sets_cache_item(): void
    {
        // Mock the cache dependency
        /** @var CacheInterface&MockObject $cacheMock */
        $cacheMock = $this->createMock(CacheInterface::class);

        // Expect the 'set' method to be called once with specific parameters
        $cacheMock->expects($this->once())
            ->method('set')
            ->with(
                $this->equalTo('testKey'),
                $this->callback(function ($cacheItem) {
                    // Check if the cache item has the expected properties
                    return $cacheItem instanceof CacheItem &&
                           $cacheItem->value === 'testValue' &&
                           is_int($cacheItem->ttl);
                }),
                $this->greaterThanOrEqual(0), // Assuming ttlAfterStale is not null and adds to the ttl
            )
            ->willReturn(true);

        /** @var RevalidatorInterface&MockObject $revalidator */
        $revalidator = $this->getMockBuilder(RevalidatorInterface::class)->getMock();
        $revalidator
            ->expects($this->never())
            ->method('revalidate');
        $revalidator
            ->expects($this->never())
            ->method('revalidateMultiple');

        // Create an instance of your class with the mocked cache
        $cache = new StaleWhileRevalidate($cacheMock, $revalidator);

        // Call the 'set' method
        $result = $cache->set('testKey', 'testValue', 3600);

        // Assert that the method returns true (as expected)
        $this->assertTrue($result);
    }

    #[Test]
    public function it_sets_cache_item_with_a_date_interval(): void
    {
        // Mock the cache dependency
        /** @var CacheInterface&MockObject $cacheMock */
        $cacheMock = $this->createMock(CacheInterface::class);

        // Expect the 'set' method to be called once with specific parameters
        $cacheMock->expects($this->once())
            ->method('set')
            ->with(
                $this->equalTo('testKey'),
                $this->callback(function ($cacheItem) {
                    // Check if the cache item has the expected properties
                    return $cacheItem instanceof CacheItem &&
                           $cacheItem->value === 'testValue' &&
                           is_int($cacheItem->ttl) &&
                           $cacheItem->ttl === 3600;
                }),
                3600,
            )
            ->willReturn(true);

        /** @var RevalidatorInterface&MockObject $revalidator */
        $revalidator = $this->getMockBuilder(RevalidatorInterface::class)->getMock();
        $revalidator
            ->expects($this->never())
            ->method('revalidate');
        $revalidator
            ->expects($this->never())
            ->method('revalidateMultiple');

        // Create an instance of your class with the mocked cache
        $cache = new StaleWhileRevalidate($cacheMock, $revalidator, 0);

        // Call the 'set' method
        $result = $cache->set('testKey', 'testValue', new DateInterval('PT1H'));

        // Assert that the method returns true (as expected)
        $this->assertTrue($result);
    }

    #[Test]
    public function it_sets_cache_item_with_an_invalid_ttl(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('A ttl for stale while revalidate caches must be provided and be a positive integer, integer (-1) given.');

        /** @var CacheInterface&MockObject $cacheMock */
        $cacheMock = $this->createMock(CacheInterface::class);

        $cacheMock
            ->expects($this->never())
            ->method('set');

        /** @var RevalidatorInterface&MockObject $revalidator */
        $revalidator = $this->getMockBuilder(RevalidatorInterface::class)->getMock();
        $revalidator
            ->expects($this->never())
            ->method('revalidate');
        $revalidator
            ->expects($this->never())
            ->method('revalidateMultiple');

        $cache = new StaleWhileRevalidate($cacheMock, $revalidator, 0);
        $cache->set('testKey', 'testValue', -1);
    }

    #[Test]
    public function it_sets_multiple_cache_items()
    {
        // Mock the cache dependency
        /** @var CacheInterface&MockObject $cacheMock */
        $cacheMock = $this->createMock(CacheInterface::class);

        // Prepare test data
        $values = [
            'key1' => new CacheItem('value1', time(), 3600),
            'key2' => new CacheItem('value2', time(), 3600),
        ];
        $ttl = 3600;

        // Expect the 'setMultiple' method to be called once with specific parameters
        $cacheMock->expects($this->once())
            ->method('setMultiple')
            ->with(
                $this->equalTo($values),
                $this->equalTo($ttl + 0), // Assuming $this->ttlAfterStale is set and not null
            )
            ->willReturn(true);

        /** @var RevalidatorInterface&MockObject $revalidator */
        $revalidator = $this->getMockBuilder(RevalidatorInterface::class)->getMock();
        $revalidator
            ->expects($this->never())
            ->method('revalidate');
        $revalidator
            ->expects($this->never())
            ->method('revalidateMultiple');

        // Create an instance of your class with the mocked cache
        $cache = new StaleWhileRevalidate($cacheMock, $revalidator, 0);

        // Call the 'setMultiple' method
        $result = $cache->setMultiple([
            'key1' => 'value1',
            'key2' => 'value2',
        ], $ttl);

        // Assert that the method returns true (as expected)
        $this->assertTrue($result);
    }

    #[Test]
    public function it_throws_exception_when_cache_contains_invalid_payload(): void
    {
        $validItem = new CacheItem('some-value', time(), 500);

        /** @var CacheInterface&MockObject $cache */
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $cache
            ->expects($this->once())
            ->method('get')
            ->with('key')
            ->willReturn('some-value');

        /** @var RevalidatorInterface&MockObject $revalidator */
        $revalidator = $this->getMockBuilder(RevalidatorInterface::class)->getMock();
        $revalidator
            ->expects($this->never())
            ->method('revalidate');

        $sWRCache = new StaleWhileRevalidate($cache, $revalidator);

        $this->expectException(InvalidStaleWhileRevalidateCacheValueException::class);
        $this->expectExceptionMessage('Value for key key of type string is not an instance of Bitpanda\CacheUtils\CacheItem');

        $this->assertNull($sWRCache->get('key'));
    }

    #[Test]
    public function it_uses_psr16_revalidator_to_revalidate_multiple_keys(): void
    {
        $expiredItem = new CacheItem('some-value', time() - 501, 500);
        $validItem = new CacheItem('some-value2', time(), 500);

        /** @var CacheInterface&MockObject $cache */
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $cache
            ->expects($this->once())
            ->method('getMultiple')
            ->with(['key1', 'key2', 'key3'])
            ->willReturn([
                'key1' => $expiredItem,
                'key2' => null,
                'key3' => $validItem,
            ]);
        $cache
            ->expects($this->once())
            ->method('setMultiple')
            ->with(['key1' => new CacheItem('new-value', time(), 500)])
            ->willReturn(true);

        /** @var CacheInterface&MockObject $sourceCache */
        $sourceCache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $sourceCache
            ->expects($this->once())
            ->method('getMultiple')
            ->with(['key1'])
            ->willReturn(['key1' => 'new-value']);

        $revalidator = new PSR16Revalidator($sourceCache);

        $sWRCache = new StaleWhileRevalidate($cache, $revalidator);

        $this->assertSame([
            'key1' => 'new-value',
            'key2' => null,
            'key3' => 'some-value2',
        ], $sWRCache->getMultiple(['key1', 'key2', 'key3']));
    }

    #[Test]
    public function it_uses_psr16_revalidator_to_revalidate_single_key(): void
    {
        $expired = new CacheItem('some-value', time() - 501, 500);
        $new = new CacheItem('new-value', time(), 500);

        /** @var CacheInterface&MockObject $cache */
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $cache
            ->expects($this->once())
            ->method('get')
            ->with('key')
            ->willReturn($expired);
        $cache
            ->expects($this->once())
            ->method('set')
            ->with('key', $new, null)
            ->willReturn(true);

        /** @var CacheInterface&MockObject $sourceCache */
        $sourceCache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $sourceCache
            ->expects($this->once())
            ->method('get')
            ->with('key')
            ->willReturn('new-value');

        $revalidator = new PSR16Revalidator($sourceCache);
        $sWRCache = new StaleWhileRevalidate($cache, $revalidator);

        $this->assertSame('new-value', $sWRCache->get('key'));
    }
}
