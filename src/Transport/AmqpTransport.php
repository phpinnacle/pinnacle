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

namespace PHPinnacle\Pinnacle\Transport;

use Amp\Promise;
use PHPinnacle\Pinnacle\Channel;
use PHPinnacle\Pinnacle\Package;
use PHPinnacle\Pinnacle\Transport;

final class AmqpTransport implements Transport
{
    /**
     * @param string $channel
     *
     * @return Promise<Channel>
     */
    public function open(string $channel): Promise
    {
        // TODO: Implement open() method.
    }

    /**
     * @param string $channel
     *
     * @return Promise<Channel>
     */
    public function subscribe(string $channel): Promise
    {
        // TODO: Implement subscribe() method.
    }

    /**
     * @param string $channel
     * @param Package $package
     *
     * @return Promise<bool>
     */
    public function send(string $channel, Package $package): Promise
    {
        // TODO: Implement send() method.
    }

    /**
     * @param string $channel
     * @param Package $package
     *
     * @return Promise<bool>
     */
    public function publish(string $channel, Package $package): Promise
    {
        // TODO: Implement publish() method.
    }
}
