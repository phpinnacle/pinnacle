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

use Amp\Promise;

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
     * @var Kernel
     */
    private $kernel;

    /**
     * @param string   $name
     * @param string[] $channels
     * @param Kernel   $kernel
     */
    public function __construct(string $name, array $channels, Kernel $kernel)
    {
        $this->name     = $name;
        $this->channels = $channels;
        $this->kernel   = $kernel;
    }

    /**
     * @return Promise
     */
    public function start(): Promise
    {
        return Promise\all([
            $this->dispatch(new Message\Open($this->name)),
            $this->dispatch(new Message\Subscribe($this->channels)),
        ]);
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
        return $this->kernel->dispatch($message, ...$arguments);
    }
}
