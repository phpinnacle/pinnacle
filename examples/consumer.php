<?php

declare(strict_types = 1);

use Amp\Loop;
use PHPinnacle\Pinnacle;
use Psr\Log\LoggerInterface;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/shared.php';

$app = (new Pinnacle\ApplicationBuilder('consumer'))
    ->transport('file:///tmp')
    ->produces(Greeting::class)
    ->handle(Hello::class, static function (Hello $hello, LoggerInterface $logger) {
        $logger->debug("Hello {$hello->name}!");

        yield new \Amp\Delayed(1000); // Emulate long calculating

        yield new Greeting($hello->name);
    })
    ->build()
;

Loop::run(function () use ($app, $argv) {
    yield $app->start();
});
