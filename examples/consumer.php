<?php

declare(strict_types = 1);

use Amp\Loop;
use PHPinnacle\Pinnacle;
use Psr\Log\LoggerInterface;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/shared.php';

$app = (new Pinnacle\ApplicationBuilder('consumer'))
    ->transport('file:///tmp')
    ->handle(Hello::class, static function (Hello $hello, LoggerInterface $logger) {
        $logger->debug("Hello {$hello->name}!");

        yield new \Amp\Delayed(1000); // Emulate long calculating

        $logger->debug('Done!');
    })
    ->build()
;

Loop::run(function () use ($app, $argv) {
    yield $app->start();
});
