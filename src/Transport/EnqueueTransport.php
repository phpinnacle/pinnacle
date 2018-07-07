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
use Interop\Queue\PsrContext;
use Interop\Queue\PsrProducer;
use Interop\Queue\PsrQueue;
use PHPinnacle\Pinnacle\Package;
use PHPinnacle\Pinnacle\Transport;

class EnqueueTransport implements Transport
{
    /**
     * @var PsrContext
     */
    private $context;

    /**
     * @var PsrProducer
     */
    private $producer;

    /**
     * @var PsrQueue[]
     */
    private $queues = [];

    /**
     * @param PsrContext $context
     */
    public function __construct(PsrContext $context)
    {
        $this->context  = $context;
        $this->producer = $context->createProducer();
    }

    /**
     * @param string $dsn
     *
     * @return self
     */
    public static function dsn(string $dsn): self
    {
        return new self(\Enqueue\dsn_to_context($dsn));
    }

    /**
     * {@inheritdoc}
     */
    public function consume(string $origin, int $interval): Iterator
    {
        $queue    = $this->createQueue($origin);
        $consumer = $this->context->createConsumer($queue);
        $emitter  = new Emitter();

        Loop::repeat($interval, static function () use ($consumer, $emitter): void {
            if (!$message = $consumer->receiveNoWait()) {
                return;
            }

            if (null === $message->getReplyTo()) {
                $consumer->reject($message);

                return;
            }

            $consumer->acknowledge($message);

            $emitter->emit(new Package(
                $message->getMessageId(),
                $message->getReplyTo(),
                $message->getBody(),
                $message->getHeaders()
            ));
        });

        return $emitter->iterate();
    }

    /**
     * {@inheritdoc}
     */
    public function send(string $destination, Package $package): Promise
    {
        $message = $this->context->createMessage($package->body(), [], $package->headers());
        $message->setReplyTo($package->origin());
        $message->setMessageId($package->id());

        $queue = $this->createQueue($destination);

        try {
            $this->producer->send($queue, $message);

            return new Success(true);
        } catch (\Throwable $error) {
            return new Failure($error);
        }
    }

    /**
     * @param string $name
     *
     * @return PsrQueue
     */
    private function createQueue(string $name): PsrQueue
    {
        return $this->queues[$name] ?? $this->queues[$name] = $this->context->createQueue($name);
    }
}
