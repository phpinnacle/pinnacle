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
use Amp\Iterator;
use Amp\Loop;
use Amp\Promise;
use Amp\Success;
use Interop\Queue\Context;
use Interop\Queue\Destination;
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
    public function open(string $channel): Iterator
    {
        return $this->consume($this->context->createQueue($channel));
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe(string $channel): Iterator
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
     * @return Iterator
     */
    private function consume(Destination $destination): Iterator
    {
        $consumer = $this->context->createConsumer($destination);
        $emitter  = new Emitter;

        Loop::repeat($this->interval, static function () use ($consumer, $emitter): void {
            if (!$message = $consumer->receiveNoWait()) {
                return;
            }

            if (null === $message->getReplyTo()) {
                $consumer->reject($message);

                return;
            }

            $promise = $emitter->emit(new Package(
                $message->getMessageId(),
                $message->getReplyTo(),
                $message->getBody(),
                $message->getHeaders()
            ));

            $promise->onResolve(static function (\Throwable $error = null) use ($consumer, $message) {
                if ($error === null) {
                    $consumer->acknowledge($message);
                }
            });
        });

        return $emitter->iterate();
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
}
