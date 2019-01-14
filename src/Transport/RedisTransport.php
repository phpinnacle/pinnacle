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

use Amp;
use Amp\Redis;
use Amp\Promise;
use PHPinnacle\Pinnacle\Channel;
use PHPinnacle\Pinnacle\Package;
use PHPinnacle\Pinnacle\Transport;

final class RedisTransport implements Transport
{
    /**
     * @var Redis\Client
     */
    private $client;

    /**
     * @var Redis\SubscribeClient
     */
    private $subscriber;

    /**
     * @param string $dsn
     */
    public function __construct(string $dsn)
    {
        $this->client     = new Redis\Client($dsn);
        $this->subscriber = new Redis\SubscribeClient($dsn);
    }

    /**
     * {@inheritdoc}
     */
    public function open(string $channel): Promise
    {
        return Amp\call(function () use ($channel) {
            $emitter = new Amp\Emitter();

            /** @var Redis\Subscription $subscription */
            $subscription = yield $this->subscriber->subscribe($channel);

            Amp\Loop::defer(function () use ($subscription, $emitter) {
                while (yield $subscription->advance()) {
                    try {
                        $package = \unserialize($subscription->getCurrent());

                        if (!$package instanceof Package) {
                            throw new \InvalidArgumentException;
                        }

                        $emitter->emit($package);
                    } catch (\Throwable $error) {
                        $emitter->fail($error);
                    }
                }
            });

            return new Channel($emitter->iterate());
        });
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
        return $this->client->publish($channel, \serialize($package));
    }

    /**
     * {@inheritdoc}
     */
    public function publish(string $channel, Package $package): Promise
    {
        return $this->client->publish($channel, \serialize($package));
    }
}
