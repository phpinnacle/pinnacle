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

use PHPinnacle\Ensign\Action;

class Application
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @param string $name
     * @param Kernel $kernel
     */
    public function __construct(string $name, Kernel $kernel)
    {
        $this->name   = $name;
        $this->kernel = $kernel;
    }

    /**
     * @return Action
     */
    public function start(): Action
    {
        return $this->dispatch(new Message\Open($this->name));
    }

    /**
     * @return Action
     */
    public function stop(): Action
    {
        return $this->dispatch(new Message\Close($this->name));
    }

    /**
     * @param object    $message
     * @param mixed  ...$arguments
     *
     * @return Action
     */
    public function dispatch(object $message, ...$arguments): Action
    {
        return $this->kernel->dispatch($message, ...$arguments);
    }

    /**
     * @param object    $message
     * @param mixed  ...$arguments
     *
     * @return Action
     */
    public function publish(object $message, ...$arguments): Action
    {
        return $this->dispatch(event($message), ...$arguments);
    }
}
