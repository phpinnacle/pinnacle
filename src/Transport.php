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

interface Transport
{
    /**
     * @param string $channel
     *
     * @return Iterator<Package>
     */
    public function open(string $channel): Iterator;

    /**
     * @param string $channel
     *
     * @return Iterator<Package>
     */
    public function subscribe(string $channel): Iterator;

    /**
     * @param string  $channel
     * @param Package $package
     *
     * @return Promise<bool>
     */
    public function send(string $channel, Package $package): Promise;

    /**
     * @param string  $channel
     * @param Package $package
     *
     * @return Promise<bool>
     */
    public function publish(string $channel, Package $package): Promise;
}
