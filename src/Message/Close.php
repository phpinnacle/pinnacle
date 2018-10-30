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

class Close
{
    /**
     * @var string
     */
    private $channel;

    /**
     * @param string $channel
     */
    public function __construct(string $channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return string
     */
    public function channel(): string
    {
        return $this->channel;
    }
}
