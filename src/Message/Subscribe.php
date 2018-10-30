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

namespace PHPinnacle\Pinnacle\Message;

final class Subscribe
{
    /**
     * @var string[]
     */
    private $channels;

    /**
     * @param string[] $channels
     */
    public function __construct(array $channels)
    {
        $this->channels = $channels;
    }

    /**
     * @return string[]
     */
    public function channels(): array
    {
        return $this->channels;
    }
}
