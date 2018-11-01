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

use Amp\Iterator;
use Amp\Loop;

/**
 * @param string $destination
 * @param object $message
 * @param array  $headers
 * @param int    $timeout
 *
 * @return Message\Send
 */
function send(string $destination, object $message, int $timeout = 0, array $headers = []): Message\Send
{
    return new Message\Send($destination, $message, $timeout, $headers);
}

/**
 * @param string $destination
 * @param object $message
 * @param array  $headers
 *
 * @return Message\Send
 */
function push(string $destination, object $message, array $headers = []): Message\Send
{
    return send($destination, $message, -1, $headers);
}

/**
 * @param object $message
 * @param array $headers
 *
 * @return Message\Publish
 */
function publish(object $message, array $headers = []): Message\Publish
{
    return new Message\Publish($message, $headers);
}

/**
 * @param string|int $interval
 * @param object     $message
 *
 * @return Message\Delay
 */
function delay($interval, object $message): Message\Delay
{
    return new Message\Delay($interval, $message);
}

/**
 * @param callable $callback
 *
 * @return void
 */
function defer(callable $callback): void
{
    Loop::unreference(Loop::defer($callback));
}

/**
 * @param Iterator $iterator
 * @param callable $callback
 *
 * @return void
 */
function iterate(Iterator $iterator, callable $callback): void
{
    defer(function () use ($iterator, $callback) {
        while (yield $iterator->advance()) {
            $current = $iterator->getCurrent();

            defer(function () use ($current, $callback) {
                yield \Amp\call($callback, $current);
            });
        }
    });
}
