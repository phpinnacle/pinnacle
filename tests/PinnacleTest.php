<?php
/**
 * This file is part of PHPinnacle/Pinnacle.
 *
 * (c) PHPinnacle Team <dev@phpinnacle.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPinnacle\Pinnacle\Tests;

use PHPUnit\Framework\TestCase;

abstract class PinnacleTest extends TestCase
{
    /**
     * @param mixed $value
     *
     * @return void
     */
    public static function assertArray($value): void
    {
        self::assertInternalType('array', $value);
    }
}
