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

use Amp\Parallel\Worker\DefaultPool;
use PHPinnacle\Ensign\Dispatcher;
use PHPinnacle\Ensign\Executor;
use PHPinnacle\Ensign\Processor;
use PHPinnacle\Pinnacle\Container;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class ApplicationBuilder
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Executor
     */
    private $executor;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var MessageRegistry
     */
    private $registry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Transport
     */
    private $transport;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name       = $name;
        $this->registry   = new MessageRegistry();
        $this->container  = new Container\EmptyContainer();
        $this->executor   = new Executor\SimpleExecutor();
        $this->serializer = new Serializer\NativeSerializer();
        $this->logger     = new Logger\ConsoleLogger($name);
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return self
     */
    public function option(string $name, $value): self
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * @param string   $command
     * @param callable $handler
     *
     * @return self
     */
    public function handle(string $command, callable $handler): self
    {
        $this->registry->handle($command, $handler);

        return $this;
    }

    /**
     * @param string   $event
     * @param callable $listener
     *
     * @return self
     */
    public function listen(string $event, callable $listener): self
    {
        $this->registry->listen($event, $listener);

        return $this;
    }

    /**
     * @param string $message
     * @param string $destination
     *
     * @return self
     */
    public function route(string $message, string $destination): self
    {
        return $this->handle($message, function (object $message, int $timeout = 0) use ($destination) {
            yield send($destination, $message, $timeout);
        });
    }

    /**
     * @param string[] ...$events
     *
     * @return self
     */
    public function produces(string ...$events): self
    {
        foreach ($events as $event) {
            $this->handle($event, function (object $message) {
                yield publish($message);
            });
        }

        return $this;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return self
     */
    public function container(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @param Serializer $serializer
     *
     * @return self
     */
    public function serializer(Serializer $serializer): self
    {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * @param Transport|string $transport
     *
     * @return self
     */
    public function transport($transport): self
    {
        $this->transport = $transport instanceof Transport ? $transport : Transport\EnqueueTransport::dsn($transport);

        return $this;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return self
     */
    public function logger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param int $number
     *
     * @return self
     */
    public function workers(int $number): self
    {
        $this->executor = new Executor\ParallelExecutor(new DefaultPool($number));

        return $this;
    }

    /**
     * @return Application
     */
    public function build(): Application
    {
        $processor  = new Processor($this->executor);
        $dispatcher = new Dispatcher($processor);
        $publisher  = new Publisher($processor);

        $config  = $this->createConfiguration();
        $gateway = $this->createGateway();
        $kernel  = $this->createKernel($dispatcher);

        $container = $this->createContainer();
        $container
            ->add($config)
            ->add($kernel)
            ->add($publisher)
        ;

        $this
            ->handle(Message\Open::class, [$gateway, 'open'])
            ->handle(Message\Subscribe::class, [$gateway, 'subscribe'])
            ->handle(Message\Close::class, [$gateway, 'close'])
            ->handle(Message\Send::class, [$gateway, 'send'])
            ->handle(Message\Publish::class, [$gateway, 'publish'])
            ->handle(Message\Confirm::class, [$gateway, 'confirm'])
            ->handle(Message\Reject::class, [$gateway, 'reject'])
        ;

        $factory = new HandlerFactory($container);

        $this->setupDispatcher($dispatcher, $factory);
        $this->setupPublisher($publisher, $factory);

        return new Application($this->name, $this->registry->channels(), $kernel);
    }

    /**
     * @return Configuration
     */
    private function createConfiguration(): Configuration
    {
        return new Configuration($this->options);
    }

    /**
     * @return Gateway
     */
    private function createGateway(): Gateway
    {
        $packer = new Packer($this->name, $this->serializer);

        return new Gateway($this->transport, new Synchronizer(), $packer);
    }

    /**
     * @param Dispatcher $dispatcher
     *
     * @return Kernel
     */
    private function createKernel(Dispatcher $dispatcher): Kernel
    {
        return new Kernel($dispatcher);
    }

    /**
     * @return Container\ProxyContainer
     */
    private function createContainer(): Container\ProxyContainer
    {
        $container = new Container\ProxyContainer($this->container);
        $container
            ->add(LoggerInterface::class, $this->logger)
            ->add(Serializer::class, $this->serializer)
            ->add(Transport::class, $this->transport)
            ->add(Context::class, function () {
                return Context\LocalContext::create($this->name);
            })
        ;

        return $container;
    }

    /**
     * @param Dispatcher     $dispatcher
     * @param HandlerFactory $factory
     *
     * @return void
     */
    private function setupDispatcher(Dispatcher $dispatcher, HandlerFactory $factory): void
    {
        foreach ($this->registry->handlers() as $command => $handler) {
            $dispatcher->register($command, $factory->make($handler));
        }
    }

    /**
     * @param Publisher      $publisher
     * @param HandlerFactory $factory
     *
     * @return void
     */
    private function setupPublisher(Publisher $publisher, HandlerFactory $factory): void
    {
        foreach ($this->registry->listeners() as $event => $listeners) {
            foreach ($listeners as $listener) {
                $publisher->listen($event, $factory->make($listener));
            }
        }
    }
}
