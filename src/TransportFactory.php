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
use PHPinnacle\Pinnacle\Transport\EnqueueContext;
use PHPinnacle\Pinnacle\Transport\EnqueueTransport;

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
        $context  = $this->createContext($dsn);
        $interval = 10;

        if ($context instanceof AmqpContext) {
            $this->declareContext($context, $name, $topics);

            $interval = 5;
        }

        return new EnqueueTransport(new EnqueueContext($context, $this->normalizer), $interval);
    }

    /**
     * @param string $dsn
     *
     * @return Context
     */
    private function createContext(string $dsn): Context
    {
        $factory = (new ConnectionFactoryFactory)->create($dsn);

        return $factory->createContext();
    }

    /**
     * @param AmqpContext $context
     * @param string      $name
     * @param array       $topics
     */
    private function declareContext(AmqpContext $context, string $name, array $topics): void
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
