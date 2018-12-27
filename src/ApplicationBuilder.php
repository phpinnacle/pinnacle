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
use PHPinnacle\Ensign\DispatcherBuilder;
use PHPinnacle\Ensign\HandlerFactory;
use PHPinnacle\Ensign\HandlerWrapper;
use PHPinnacle\Ensign\Wrapper;
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
     * @var array
     */
    private $channels = [];

    /**
     * @var HandlerFactory
     */
    private $factory;

    /**
     * @var DispatcherBuilder
     */
    private $builder;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $dsn;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name       = $name;
        $this->factory    = new HandlerFactory;
        $this->builder    = new DispatcherBuilder($this->factory);
        $this->container  = new Container\EmptyContainer;
        $this->serializer = new Serializer\NativeSerializer;
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
        $this->builder->register($command, $handler);

        return $this;
    }

    /**
     * @param string   $event
     * @param callable $handler
     *
     * @return self
     */
    public function listen(string $event, callable $handler): self
    {
        $this->channels[] = $event;

        return $this->handle($event, $handler);
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
     * @param string ...$events
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
     * @param HandlerWrapper $wrapper
     *
     * @return self
     */
    public function wrap(HandlerWrapper $wrapper): self
    {
        $this->factory->with($wrapper);

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
     * @param string $dsn
     *
     * @return self
     */
    public function transport(string $dsn): self
    {
        $this->dsn = $dsn;

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
     * @return Application
     */
    public function build(): Application
    {
        $this->buildGateway();
        $this->buildContainer();

        return new Application($this->name, $this->channels, $this->builder->build());
    }

    /**
     * @return void
     */
    private function buildGateway(): void
    {
        $transport = (new TransportFactory)->create($this->dsn, $this->name, $this->channels);
        $packer    = new Packer($this->name, $this->serializer);
        $gateway   = new Gateway($transport, new Synchronizer, $packer);

        $this
            ->handle(Message\Open::class, [$gateway, 'open'])
            ->handle(Message\Subscribe::class, [$gateway, 'subscribe'])
            ->handle(Message\Close::class, [$gateway, 'close'])
            ->handle(Message\Send::class, [$gateway, 'send'])
            ->handle(Message\Publish::class, [$gateway, 'publish'])
            ->handle(Message\Confirm::class, [$gateway, 'confirm'])
            ->handle(Message\Reject::class, [$gateway, 'reject'])
        ;
    }

    /**
     * @return void
     */
    private function buildContainer(): void
    {
        $container = new Container\ProxyContainer($this->container);
        $container
            ->add(LoggerInterface::class, $this->logger)
            ->add(Serializer::class, $this->serializer)
            ->add(Context::class, function () {
                return Context\LocalContext::create($this->name);
            })
            ->add(Configuration::class, new Configuration($this->options))
        ;

        $this
            ->wrap(new Wrapper\ContainerWrapper($container))
        ;
    }
}
