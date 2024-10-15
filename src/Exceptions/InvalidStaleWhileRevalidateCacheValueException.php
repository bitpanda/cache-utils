<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils\Exceptions;

use InvalidArgumentException;
use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;

class InvalidStaleWhileRevalidateCacheValueException extends InvalidArgumentException implements PsrInvalidArgumentException
{
}
