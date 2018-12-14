<?php
/**
 * This file is part of PHPinnacle/Pinnacle.
 *
 * (c) PHPinnacle Team <dev@phpinnacle.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace PHPinnacle\Pinnacle;

final class Time
{
    /**
     * @return int
     * @throws \Exception
     */
    public static function seconds(): int
    {
        return static::now()->getTimestamp();
    }

    /**
     * @param \DateInterval $interval
     *
     * @return int
     * @throws \Exception
     */
    public static function milliseconds(\DateInterval $interval = null): int
    {
        $time = $interval ? static::now('@0')->add($interval) : static::now();

        return $time->getTimestamp() * 1000 + (int) $time->format('v');
    }

    /**
     * @param string $time
     *
     * @return \DateTimeImmutable
     * @throws \Exception
     */
    public static function now(string $time = null): \DateTimeImmutable
    {
        return new \DateTimeImmutable($time ?: 'now');
    }
}
