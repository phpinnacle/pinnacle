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

use Amp\Promise;

interface Transport
{
    /**
     * @param string $channel
     *
     * @return Promise<Channel>
     */
    public function open(string $channel): Promise;

    /**
     * @param string  $channel
     * @param Package $package
     *
     * @return Promise<bool>
     */
    public function send(string $channel, Package $package): Promise;
}
