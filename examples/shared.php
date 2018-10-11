<?php

declare(strict_types = 1);

class Hello
{
    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

class Greeting extends Hello
{
}
