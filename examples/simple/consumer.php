<?php

declare(strict_types = 1);

use Amp\Loop;
use PHPinnacle\Pinnacle;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/shared.php';

$app = (new Pinnacle\ApplicationBuilder('consumer'))
    ->transport('file:///tmp')
    ->produces(Greeting::class)
    ->handle(Hello::class, [ConsumerService::class, 'sayHello'])
    ->build()
;

Loop::run(function () use ($app, $argv) {
    yield $app->start();
});
