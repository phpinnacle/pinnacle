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
use Interop\Queue\PsrDestination;
use PHPinnacle\Pinnacle\Package;
use PHPinnacle\Pinnacle\Transport;

final class EnqueueTransport implements Transport
{
    /**
     * @var PsrContext
     */
    private $context;

    /**
     * @var int
     */
    private $interval;

    /**
     * @param PsrContext $context
     * @param int        $interval
     */
    public function __construct(PsrContext $context, int $interval = 10)
    {
        $this->context  = $context;
        $this->interval = $interval;
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
    public function open(string $channel): Iterator
    {
        $name = $this->sanitizeName($channel);

        return $this->consume($this->context->createQueue($name));
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe(string $channel): Iterator
    {
        $name = $this->sanitizeName($channel);

        return $this->consume($this->context->createTopic($name));
    }

    /**
     * {@inheritdoc}
     */
    public function send(string $channel, Package $package): Promise
    {
        $name = $this->sanitizeName($channel);

        return $this->emit($this->context->createQueue($name), $package);
    }

    /**
     * {@inheritdoc}
     */
    public function publish(string $channel, Package $package): Promise
    {
        $name = $this->sanitizeName($channel);

        return $this->emit($this->context->createTopic($name), $package);
    }

    /**
     * @param PsrDestination $destination
     *
     * @return Iterator
     */
    private function consume(PsrDestination $destination): Iterator
    {
        $consumer = $this->context->createConsumer($destination);
        $emitter  = new Emitter();

        Loop::repeat($this->interval, static function () use ($consumer, $emitter): void {
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
     * @param PsrDestination $destination
     * @param Package        $package
     *
     * @return Promise
     */
    private function emit(PsrDestination $destination, Package $package): Promise
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
     * @param string $name
     * @return string
     */
    private function sanitizeName(string $name): string
    {
        return strtolower(str_replace('\\', '_', $name));
    }
}
