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

use Amp\Deferred;
use Amp\Loop;
use Amp\Promise;
use Amp\Success;
use Psr\Log\LoggerInterface;

class Gateway
{
    const
        INTERVAL = 'interval'
    ;

    /**
     * @var Transport
     */
    private $transport;

    /**
     * @var Configuration
     */
    private $config;

    /**
     * @var Packer
     */
    private $packer;

    /**
     * @var Deferred[]
     */
    private $waiters = [];

    /**
     * @param Transport     $transport
     * @param Configuration $config
     * @param Packer        $packer
     */
    public function __construct(Transport $transport, Configuration $config, Packer $packer)
    {
        $this->transport = $transport;
        $this->config    = $config;
        $this->packer    = $packer;
    }

    /**
     * @param Message\Open    $command
     * @param Kernel          $kernel
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function open(Message\Open $command, Kernel $kernel, LoggerInterface $logger)
    {
        $origin  = $command->origin();
        $timeout = (int) $this->config[self::INTERVAL];

        $logger->info('Start consuming messages for "{origin}"', [
            'origin' => $command->origin(),
        ]);

        $iterator = $this->transport->consume($origin, $timeout);

        $watcher = Loop::defer(function () use ($iterator, $kernel) {
            while (yield $iterator->advance()) {
                /** @var Package $package */
                $package = $iterator->getCurrent();

                $watcher = Loop::defer(function () use ($package, $kernel) {
                    $message = $this->packer->unpack($package);
                    $context = Context\RemoteContext::create($package);

                    try {
                        yield $kernel->dispatch($message, $context);

                        $reply = new Message\Confirm($context->id());
                    } catch (\Throwable $error) {
                        $reply = new Message\Reject($context->id(), $error);
                    }

                    if ($message instanceof Contract\NoConfirmation) {
                        return;
                    }

                    yield $this->transport->send($context->origin(), $this->packer->pack($reply));
                });

                Loop::unreference($watcher);
            }
        });

        Loop::unreference($watcher);
    }

    /**
     * @param Message\Close   $command
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function close(Message\Close $command, LoggerInterface $logger)
    {
        $logger->info('Stop consuming messages for "{origin}"', [
            'origin' => $command->origin(),
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

        yield $this->transport->send($command->destination(), $package);

        $logger->info('Message "{id}" sent to "{destination}".', [
            'id'          => $package->id(),
            'destination' => $command->destination(),
        ]);

        return $this->wait($package->id(), $command->timeout());
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
            $this->resolve($command->id());

            $logger->info('Message "{id}" resolved with no errors.', [
                'id' => $command->id(),
            ]);
        } catch (Exception\ResolutionException $error) {
            $logger->info('Message "{id}" cant be resolved.', [
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
            $this->resolve($command->id(), Exception\RemoteException::reject($command));

            $logger->warning('Message "{id}" resolved with error: [{code}] {text}.', [
                'id'   => $command->id(),
                'code' => $command->code(),
                'text' => $command->text(),
            ]);
        } catch (Exception\ResolutionException $error) {
            $logger->info('Message "{id}" cant be resolved.', [
                'id' => $command->id(),
            ]);

            throw $error;
        }
    }

    /**
     * @param string $id
     * @param int    $timeout
     *
     * @return Promise
     */
    private function wait(string $id, int $timeout): Promise
    {
        if ($timeout < 0) {
            return new Success();
        }

        $deferred = new Deferred();

        $this->waiters[$id] = &$deferred;

        if ($timeout > 0) {
            Loop::delay($timeout, function () use ($id) {
                if ($deferred = $this->waiters[$id] ?? null) {
                    $deferred->fail(new Exception\TimeoutException($id));
                }
            });
        }

        return $deferred->promise();
    }

    /**
     * @param string     $id
     * @param \Throwable $error
     *
     * @return void
     * @throws Exception\ResolutionException
     */
    private function resolve(string $id, \Throwable $error = null)
    {
        if (!$deferred = $this->waiters[$id] ?? null) {
            throw new Exception\ResolutionException($id);
        }

        unset($this->waiters[$id]);

        if ($error) {
            $deferred->fail($error);
        } else {
            $deferred->resolve();
        }
    }
}
