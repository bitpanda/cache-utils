<?php

declare(strict_types=1);

namespace Bitpanda\CacheUtils;

use DateInterval;

class CacheHelper
{
    public static function dateIntervalToSeconds(DateInterval $dateInterval): int
    {
        $yearSeconds = $dateInterval->y * 31536000;
        $monthSeconds = $dateInterval->m * 2592000;
        $daySeconds = $dateInterval->d * 86400;
        $hourSeconds = $dateInterval->h * 3600;
        $minuteSeconds = $dateInterval->i * 60;
        $seconds = $dateInterval->s;

        return $yearSeconds + $monthSeconds + $daySeconds + $hourSeconds + $minuteSeconds + $seconds;
    }
}
