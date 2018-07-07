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

interface Context
{
    /**
     * @return string
     */
    public function id(): string;

    /**
     * @return string
     */
    public function origin(): string;

    /**
     * @return array
     */
    public function headers(): array;
}
