<?php

declare(strict_types = 1);

use Amp\Loop;
use PHPinnacle\Pinnacle;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/shared.php';

$name = $argv[1] ?? 'John';

$app = (new Pinnacle\ApplicationBuilder(sprintf('sender:%s', md5($name))))
    ->transport('file:///tmp')
    ->route(Hello::class, 'consumer')
    ->listen(Greeting::class, [SenderService::class, 'replyGreeting'])
    ->build()
;

Loop::run(function () use ($app, $name) {
    yield $app->start();
    yield $app->dispatch(new Hello($name));

    Loop::stop();
});
