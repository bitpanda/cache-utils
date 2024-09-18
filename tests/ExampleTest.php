<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils\Tests;

use Bitpanda\CacheUtils\ExampleFile;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Bitpanda\CacheUtils\ExampleFile
 */
class ExampleTest extends TestCase
{
    /**
     * @test
     * @covers \Bitpanda\CacheUtils\ExampleFile
     */
    public function it_tests_something(): void
    {
        $x = new ExampleFile();

        $this->assertSame(ExampleFile::class, get_class($x));
    }
}
