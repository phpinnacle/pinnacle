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
    private $origin;

    /**
     * @param string $origin
     */
    public function __construct(string $origin)
    {
        $this->origin = $origin;
    }

    /**
     * @return string
     */
    public function origin(): string
    {
        return $this->origin;
    }
}
