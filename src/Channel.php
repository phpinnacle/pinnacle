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

use Amp\Iterator;
use Amp\Promise;

final class Channel
{
    /**
     * @var Iterator
     */
    private $receiver;

    /**
     * @param Iterator $receiver
     */
    public function __construct(Iterator $receiver)
    {
        $this->receiver = $receiver;
    }

    /**
     * @return Promise
     * @throws \Throwable
     */
    public function advance(): Promise
    {
        return $this->receiver->advance();
    }

    /**
     * @return Package
     * @throws \Throwable
     */
    public function receive(): Package
    {
        return $this->receiver->getCurrent();
    }
}
