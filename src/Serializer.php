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

interface Serializer
{
    /**
     * @param object $message
     *
     * @return string
     */
    public function serialize(object $message): string;

    /**
     * @param string $body
     *
     * @return object
     */
    public function unserialize(string $body): object;
}
