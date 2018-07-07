<?php

namespace PHPinnacle\Pinnacle;

use PHPinnacle\Ensign\Action;
use PHPinnacle\Ensign\Dispatcher;

class Kernel
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param object   $message
     * @param array ...$arguments
     *
     * @return Action
     */
    public function dispatch(object $message, ...$arguments): Action
    {
        return $this->dispatcher->dispatch($message, ...$arguments);
    }
}
