<?php

declare(strict_types = 1);

use Amp\Loop;
use PHPinnacle\Pinnacle;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/shared.php';

$app = (new Pinnacle\ApplicationBuilder('sender'))
    ->transport('file:///tmp')
    ->route(Hello::class, 'consumer')
    ->build()
;

Loop::run(function () use ($app, $argv) {
    yield $app->start();
    yield $app->dispatch(new Hello($argv[1]));

    Loop::stop();
});
