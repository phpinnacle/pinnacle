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

use Enqueue\ConnectionFactoryFactory;
use Interop\Amqp\AmqpContext;
use Interop\Amqp\AmqpQueue;
use Interop\Amqp\AmqpTopic;
use Interop\Amqp\Impl\AmqpBind;
use Interop\Queue\Context;
use PHPinnacle\Ridge\Client;

final class TransportFactory
{
    /**
     * @var Normalizer
     */
    private $normalizer;

    /**
     * @param Normalizer $normalizer
     */
    public function __construct(Normalizer $normalizer = null)
    {
        $this->normalizer = $normalizer ?: new Normalizer;
    }

    /**
     * @param string $dsn
     * @param string $name
     * @param array  $topics
     *
     * @return Transport
     */
    public function create(string $dsn, string $name, array $topics = []): Transport
    {
        $scheme = parse_url($dsn, PHP_URL_SCHEME);

        switch ($scheme) {
            case 'mem':
            case 'memory':
                return new Transport\InMemoryTransport;
            case 'redis':
                return new Transport\RedisTransport($dsn);
            case 'amqp':
                $client = Client::create($dsn);

                return new Transport\RidgeTransport($client);
            default:
                throw new \InvalidArgumentException;
        }
    }

    /**
     * @param Client $client
     * @param string $name
     * @param array  $topics
     */
    private function setup(Client $client, string $name, array $topics): void
    {
        $name   = $this->normalizer->normalize($name);
        $topics = array_map(function (string $topic) {
            return $this->normalizer->normalize($topic);
        }, $topics);

        /** @var AmqpQueue $queue */
        $queue = $context->createQueue($name);
        $queue->setFlags(AmqpQueue::FLAG_DURABLE | AmqpQueue::FLAG_IFUNUSED);

        $context->declareQueue($queue);

        foreach ($topics as $channel) {
            /** @var AmqpTopic $queue */
            $topic = $context->createTopic($channel);
            $topic->setFlags(AmqpTopic::FLAG_DURABLE | AmqpTopic::FLAG_IFUNUSED);
            $topic->setType(AmqpTopic::TYPE_FANOUT);

            $queue = $context->createQueue(sprintf('%s:%s', $name, $channel));

            $context->declareQueue($queue);
            $context->declareTopic($topic);
            $context->bind(new AmqpBind($queue, $topic));
        }
    }
}
