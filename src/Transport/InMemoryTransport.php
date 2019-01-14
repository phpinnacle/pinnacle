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

use Amp\Emitter;
use Amp\Iterator;
use Amp\Promise;
use Amp\Success;
use PHPinnacle\Pinnacle\Channel;
use PHPinnacle\Pinnacle\Package;
use PHPinnacle\Pinnacle\Transport;

final class InMemoryTransport implements Transport
{
    /**
     * @var Emitter[]
     */
    private $emitters = [];

    /**
     * @var Iterator[]
     */
    private $iterators = [];

    /**
     * {@inheritdoc}
     */
    public function open(string $channel): Promise
    {
        return new Success(new Channel($this->iterator($channel)));
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe(string $channel): Promise
    {
        return $this->open($channel);
    }

    /**
     * {@inheritdoc}
     */
    public function send(string $channel, Package $package): Promise
    {
        $emitter = $this->emitter($channel);

        return $emitter->emit($package);
    }

    /**
     * {@inheritdoc}
     */
    public function publish(string $channel, Package $package): Promise
    {
        return $this->send($channel, $package);
    }

    /**
     * @param string $channel
     *
     * @return Emitter
     */
    private function emitter(string $channel): Emitter
    {
        if (!isset($this->emitters[$channel])) {
            $this->setup($channel);
        }

        return $this->emitters[$channel];
    }

    /**
     * @param string $channel
     *
     * @return Iterator
     */
    private function iterator(string $channel): Iterator
    {
        if (!isset($this->iterators[$channel])) {
            $this->setup($channel);
        }

        return $this->iterators[$channel];
    }

    /**
     * @param string $channel
     */
    private function setup(string $channel): void
    {
        $emitter = new Emitter;

        $this->emitters[$channel]  = $emitter;
        $this->iterators[$channel] = $emitter->iterate();
    }
}
