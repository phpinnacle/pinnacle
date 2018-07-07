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

abstract class ClassFinder
{
    /**
     * @param string $pattern
     *
     * @return iterable
     */
    public function find(string $pattern): iterable
    {
        $classes = $this->findClasses($pattern);

        foreach ($classes as $class) {
            if ($this->match($pattern, $class)) {
                yield $class;
            }
        }
    }

    /**
     * @param string $pattern
     * @param string $string
     *
     * @return bool
     */
    protected function match(string $pattern, string $string): bool
    {
        return \fnmatch($pattern, $string, \FNM_PATHNAME | \FNM_CASEFOLD | \FNM_NOESCAPE);
    }

    /**
     * @param string $pattern
     *
     * @return iterable
     */
    abstract protected function findClasses(string $pattern): iterable;
}
