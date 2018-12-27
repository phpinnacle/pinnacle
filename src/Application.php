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

use Amp\Loop;
use Amp\Promise;
use Amp\Success;
use PHPinnacle\Ensign\Dispatcher;

final class Application
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $channels;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @param string     $name
     * @param string[]   $channels
     * @param Dispatcher $dispatcher
     */
    public function __construct(string $name, array $channels, Dispatcher $dispatcher)
    {
        $this->name       = $name;
        $this->channels   = $channels;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return Promise
     */
    public function start(): Promise
    {
        Loop::defer(function () {
            yield Promise\all([
                $this->dispatch(new Message\Open($this->name)),
                $this->dispatch(new Message\Subscribe($this->channels)),
            ]);
        });

        return new Success;
    }

    /**
     * @return Promise
     */
    public function stop(): Promise
    {
        return $this->dispatch(new Message\Close($this->name));
    }

    /**
     * @param object    $message
     * @param mixed  ...$arguments
     *
     * @return Promise
     */
    public function dispatch(object $message, ...$arguments): Promise
    {
        return $this->dispatcher->dispatch($message, ...$arguments);
    }
}
