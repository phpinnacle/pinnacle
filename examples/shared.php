<?php

declare(strict_types = 1);

use Psr\Log\LoggerInterface;

class Hello
{
    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

class Greeting extends Hello
{
}

class SenderService
{
    public static function replyGreeting(Greeting $greeting, LoggerInterface $logger)
    {
        $logger->info("Greeting from {$greeting->name}!");
    }
}

class ConsumerService
{
    public static function sayHello(Hello $hello, LoggerInterface $logger)
    {
        $logger->info("Hello {$hello->name}!");

        yield new \Amp\Delayed(1000); // Emulate long calculating

        yield new Greeting($hello->name);
    }
}
