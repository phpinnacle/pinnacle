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
use Amp\Promise;
use Amp\Loop;
use PHPinnacle\Pinnacle\Channel;
use PHPinnacle\Pinnacle\Package;
use PHPinnacle\Pinnacle\Transport;
use PHPinnacle\Ridge\Client;
use PHPinnacle\Ridge\Message;

final class RidgeTransport implements Transport
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function open(string $queue): Promise
    {
        return Amp\call(function () use ($queue) {
            $emitter = new Amp\Emitter();
            /** @var \PHPinnacle\Ridge\Channel $channel */
            $channel  = yield $this->client->channel();

            yield $channel->consume(function (Message $message) use ($emitter) {
                try {
                    $package = \unserialize($message->content());

                    if (!$package instanceof Package) {
                        throw new \InvalidArgumentException;
                    }

                    $emitter->emit($package);
                } catch (\Throwable $error) {
                    $emitter->fail($error);
                }
            }, $queue);

            return new Channel($emitter->iterate());
        });
    }

    /**
     * {@inheritdoc}
     */
    public function send(string $queue, Package $package): Promise
    {
        return Amp\call(function () use ($queue, $package) {
            /** @var \PHPinnacle\Ridge\Channel $channel */
            $channel = yield $this->client->channel();

            return $channel->publish(\serialize($package), $queue);
        });
    }

    /**
     * @param string $queue
     * @param array  $topics
     */
    public function setup(string $queue, array $topics)
    {
        Amp\asyncCall(function () use ($queue, $topics) {

        });
    }
}
