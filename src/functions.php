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
    return new Message\Send($destination, $message, -1, $headers);
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
 * @param object $message
 *
 * @return Message\Event
 */
function event(object $message): Message\Event
{
    return new Message\Event($message);
}
