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

final class Normalizer
{
    /**
     * @param string $string
     *
     * @return string
     */
    public function normalize(string $string): string
    {
        return trim(mb_strtolower(str_replace('\\', '_', $string)));
    }
}
