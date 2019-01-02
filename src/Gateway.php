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

use Amp\Promise;
use Psr\Log\LoggerInterface;

final class Gateway
{
    /**
     * @var Transport
     */
    private $transport;

    /**
     * @var Synchronizer
     */
    private $synchronizer;

    /**
     * @var Packer
     */
    private $packer;

    /**
     * @param Transport    $transport
     * @param Synchronizer $synchronizer
     * @param Packer       $packer
     */
    public function __construct(Transport $transport, Synchronizer $synchronizer, Packer $packer)
    {
        $this->transport    = $transport;
        $this->synchronizer = $synchronizer;
        $this->packer       = $packer;
    }

    /**
     * @param Message\Open    $command
     * @param LoggerInterface $logger
     *
     * @return \Generator
     * @throws \Throwable
     */
    public function open(Message\Open $command, LoggerInterface $logger)
    {
        $logger->debug('Start consuming messages for "{channel}"', [
            'channel' => $command->channel(),
        ]);

        $channel = $this->transport->open($command->channel());

        while (yield $channel->advance()) {
            $package = $channel->getCurrent();

            $message = $this->packer->unpack($package);
            $context = Context\RemoteContext::create($package);

            yield function () use ($message, $context, $channel) {
                try {
                    yield $message => $context;

                    $reply = new Message\Confirm($context->id());
                } catch (\Throwable $error) {
                    $reply = new Message\Reject($context->id(), $error);
                }

                if ($message instanceof Contract\NoConfirmation) {
                    return;
                }

                yield $this->transport->send($context->origin(), $this->packer->pack($reply));
            };
        }
    }

    /**
     * @param Message\Subscribe $command
     * @param LoggerInterface   $logger
     *
     * @return \Generator
     * @throws \Throwable
     */
    public function subscribe(Message\Subscribe $command, LoggerInterface $logger)
    {
        foreach ($command->channels() as $channel) {
            $logger->debug('Subscribed to channel "{channel}"', [
                'channel' => $channel,
            ]);

            $iterator = $this->transport->subscribe($channel);

            yield function () use ($iterator) {
                while (yield $iterator->advance()) {
                    $package = $iterator->getCurrent();

                    $message = $this->packer->unpack($package);
                    $context = Context\RemoteContext::create($package);

                    yield async($message, $context);
                }
            };
        }
    }

    /**
     * @param Message\Close   $command
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function close(Message\Close $command, LoggerInterface $logger)
    {
        $logger->debug('Stop consuming messages for "{channel}"', [
            'channel' => $command->channel(),
        ]);
    }

    /**
     * @param Message\Send    $command
     * @param LoggerInterface $logger
     *
     * @return Promise
     */
    public function send(Message\Send $command, LoggerInterface $logger)
    {
        $package = $this->packer->pack($command->message(), $command->headers());

        yield $this->transport->send($command->channel(), $package);

        $logger->debug('Message "{id}" sent to "{channel}".', [
            'id'      => $package->id(),
            'channel' => $command->channel(),
        ]);

        return $this->synchronizer->wait($package->id(), $command->timeout());
    }

    /**
     * @param Message\Publish $command
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function publish(Message\Publish $command, LoggerInterface $logger)
    {
        $package = $this->packer->pack($command->message(), $command->headers());

        yield $this->transport->publish($command->channel(), $package);

        $logger->debug('Message "{id}" published to "{channel}".', [
            'id'      => $package->id(),
            'channel' => $command->channel(),
        ]);
    }

    /**
     * @param Message\Confirm $command
     * @param LoggerInterface $logger
     *
     * @return void
     * @throws Exception\ResolutionException
     */
    public function confirm(Message\Confirm $command, LoggerInterface $logger)
    {
        try {
            $this->synchronizer->resolve($command->id());

            $logger->debug('Message "{id}" resolved with no errors.', [
                'id' => $command->id(),
            ]);
        } catch (Exception\ResolutionException $error) {
            $logger->warning('Message "{id}" cant be confirmed.', [
                'id' => $command->id(),
            ]);

            throw $error;
        }
    }

    /**
     * @param Message\Reject  $command
     * @param LoggerInterface $logger
     *
     * @return void
     * @throws Exception\ResolutionException
     */
    public function reject(Message\Reject $command, LoggerInterface $logger)
    {
        try {
            $this->synchronizer->reject($command->id(), Exception\RemoteException::reject($command));

            $logger->error('Message "{id}" resolved with error: [{code}] {text}.', [
                'id'   => $command->id(),
                'code' => $command->code(),
                'text' => $command->text(),
            ]);
        } catch (Exception\ResolutionException $error) {
            $logger->warning('Message "{id}" cant be rejected.', [
                'id' => $command->id(),
            ]);

            throw $error;
        }
    }
}
