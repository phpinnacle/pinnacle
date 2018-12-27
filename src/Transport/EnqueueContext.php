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

use Interop\Queue\Consumer;
use Interop\Queue\Context;
use Interop\Queue\Destination;
use Interop\Queue\Message;
use Interop\Queue\Producer;
use Interop\Queue\Queue;
use Interop\Queue\SubscriptionConsumer;
use Interop\Queue\Topic;
use PHPinnacle\Pinnacle\Normalizer;

final class EnqueueContext implements Context
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var Normalizer
     */
    private $normalizer;

    /**
     * @param Context             $context
     * @param Normalizer $normalizer
     */
    public function __construct(Context $context, Normalizer $normalizer)
    {
        $this->context    = $context;
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function createMessage(string $body = '', array $properties = [], array $headers = []): Message
    {
        return $this->context->createMessage($body, $properties, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function createTopic(string $topicName): Topic
    {
        return $this->context->createTopic($this->normalizer->normalize($topicName));
    }

    /**
     * {@inheritdoc}
     */
    public function createQueue(string $queueName): Queue
    {
        return $this->context->createQueue($this->normalizer->normalize($queueName));
    }

    /**
     * {@inheritdoc}
     */
    public function createTemporaryQueue(): Queue
    {
        return $this->context->createTemporaryQueue();
    }

    /**
     * {@inheritdoc}
     */
    public function createProducer(): Producer
    {
        return $this->context->createProducer();
    }

    /**
     * {@inheritdoc}
     */
    public function createConsumer(Destination $destination): Consumer
    {
        return $this->context->createConsumer($destination);
    }

    /**
     * {@inheritdoc}
     */
    public function createSubscriptionConsumer(): SubscriptionConsumer
    {
        return $this->context->createSubscriptionConsumer();
    }

    /**
     * {@inheritdoc}
     */
    public function purgeQueue(Queue $queue): void
    {
        $this->context->purgeQueue($queue);
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        $this->context->close();
    }
}
