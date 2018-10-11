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

final class MessageRegistry
{
    /**
     * @var callable[]
     */
    private $handlers = [];

    /**
     * @var callable[][]
     */
    private $listeners = [];

    /**
     * @param string   $command
     * @param callable $handler
     *
     * @return self
     */
    public function handle(string $command, callable $handler): self
    {
        $this->handlers[$command] = $handler;

        return $this;
    }

    /**
     * @param string   $event
     * @param callable $listener
     *
     * @return self
     */
    public function listen(string $event, callable $listener): self
    {
        $this->listeners[$event][] = $listener;

        return $this;
    }

    /**
     * @return callable[]
     */
    public function handlers(): array
    {
        return $this->handlers;
    }

    /**
     * @return callable[][]
     */
    public function listeners(): array
    {
        return $this->listeners;
    }

    /**
     * @return string[]
     */
    public function channels(): array
    {
        return \array_keys($this->listeners);
    }
}
