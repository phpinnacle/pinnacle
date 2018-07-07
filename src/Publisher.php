<?php

namespace PHPinnacle\Pinnacle;

use Amp\Promise;
use PHPinnacle\Ensign\Processor;

class Publisher
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
     * @param Message\Event $event
     * @param Context       $context
     *
     * @return Promise
     */
    public function publish(Message\Event $event, Context $context): Promise
    {
        $message = $event->message();

        return Promise\any(\array_map(function ($listener) use ($message, $context) {
            return $this->processor->execute($listener, [$message, $context]);
        }, $this->listeners[\get_class($message)] ?? []));
    }
}
