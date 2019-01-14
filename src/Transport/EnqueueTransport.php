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
use Amp\Failure;
use Amp\Loop;
use Amp\Promise;
use Amp\Success;
use Interop\Queue\Consumer;
use Interop\Queue\Context;
use Interop\Queue\Destination;
use Interop\Queue\Exception\SubscriptionConsumerNotSupportedException;
use Interop\Queue\Message;
use Interop\Queue\Topic;
use PHPinnacle\Pinnacle\Channel;
use PHPinnacle\Pinnacle\Package;
use PHPinnacle\Pinnacle\Transport;

final class EnqueueTransport implements Transport
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var int
     */
    private $interval;

    /**
     * @param Context $context
     * @param int     $interval
     */
    public function __construct(Context $context, int $interval = 10)
    {
        $this->context  = $context;
        $this->interval = $interval;
    }

    /**
     * {@inheritdoc}
     */
    public function open(string $channel): Channel
    {
        return $this->consume($this->context->createQueue($channel));
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe(string $channel): Channel
    {
        return $this->consume($this->context->createTopic($channel));
    }

    /**
     * {@inheritdoc}
     */
    public function send(string $channel, Package $package): Promise
    {
        return $this->emit($this->context->createQueue($channel), $package);
    }

    /**
     * {@inheritdoc}
     */
    public function publish(string $channel, Package $package): Promise
    {
        return $this->emit($this->context->createTopic($channel), $package);
    }

    /**
     * @param Destination $destination
     *
     * @return Channel
     */
    private function consume(Destination $destination): Channel
    {
        $consumer = $this->context->createConsumer($destination);
        $receiver = new Emitter;
        $defer    = true;

        if ($destination instanceof Topic) {
            try {
                $subscription = $this->context->createSubscriptionConsumer();
                $subscription->subscribe($consumer, function (Message $message, Consumer $consumer) use ($receiver) {
                    $this->doProcess($message, $consumer, $receiver);
                });

                $defer = false;
            } catch (SubscriptionConsumerNotSupportedException $error) {
            }
        }

        $callback = function () use ($consumer, $receiver, &$callback) {
            Loop::defer($callback);

            $this->process($consumer, $receiver);
        };

        if ($defer) {
            Loop::defer($callback);
        }

        return new Channel($receiver->iterate());
    }

    /**
     * @param Destination $destination
     * @param Package     $package
     *
     * @return Promise
     */
    private function emit(Destination $destination, Package $package): Promise
    {
        try {
            $message = $this->context->createMessage($package->body(), [], $package->headers());
            $message->setReplyTo($package->origin());
            $message->setMessageId($package->id());

            $this->context->createProducer()->send($destination, $message);

            return new Success(true);
        } catch (\Throwable $error) {
            return new Failure($error);
        }
    }

    /**
     * @param Consumer $consumer
     * @param Emitter  $receiver
     *
     * @return void
     */
    private function process(Consumer $consumer, Emitter $receiver): void
    {
        if (!$message = $consumer->receiveNoWait()) {
            return;
        }

        $this->doProcess($message, $consumer, $receiver);
    }

    /**
     * @param Message  $message
     * @param Consumer $consumer
     * @param Emitter  $receiver
     *
     * @return void
     */
    private function doProcess(Message $message, Consumer $consumer, Emitter $receiver): void
    {
        if (null === $message->getReplyTo()) {
            $consumer->reject($message);

            return;
        }

        $receiver->emit(new Package(
            $message->getMessageId(),
            $message->getReplyTo(),
            $message->getBody(),
            $message->getHeaders()
        ));

        $consumer->acknowledge($message);
    }
}
