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
     * @param string $origin
     * @param int    $interval
     *
     * @return Iterator<Package>
     */
    public function consume(string $origin, int $interval): Iterator;

    /**
     * @param string  $destination
     * @param Package $package
     *
     * @return Promise<bool>
     */
    public function send(string $destination, Package $package): Promise;
}
