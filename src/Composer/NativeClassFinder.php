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

namespace PHPinnacle\Pinnacle\Composer;

class NativeClassFinder extends ClassFinder
{
    /**
     * @param string $pattern
     *
     * @return iterable
     */
    protected function findClasses(string $pattern): iterable
    {
        return \get_declared_classes();
    }
}
