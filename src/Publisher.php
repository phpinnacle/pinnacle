<?php

namespace PHPinnacle\Pinnacle;

use Amp\Promise;
use PHPinnacle\Ensign\Processor;

final class Publisher
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var callable[][]
     */
    private $listeners = [];

    /**
     * @param Processor $processor
     */
    public function __construct(Processor $processor)
    {
        $this->processor = $processor;
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
     * @param object  $event
     * @param Context $context
     *
     * @return Promise
     */
    public function publish(object $event, Context $context): Promise
    {
        return Promise\any(\array_map(function ($listener) use ($event, $context) {
            return $this->processor->execute($listener, [$event, $context]);
        }, $this->listeners[\get_class($event)] ?? []));
    }
}
