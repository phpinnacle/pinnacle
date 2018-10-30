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

namespace PHPinnacle\Pinnacle\Serializer;

use PHPinnacle\Pinnacle\Serializer;

final class NativeSerializer implements Serializer
{
    /**
     * {@inheritdoc}
     */
    public function serialize(object $message): string
    {
        return \serialize($message);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize(string $data): object
    {
        return \unserialize($data);
    }
}
