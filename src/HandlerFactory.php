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

class HandlerFactory
{
    /**
     * @var Instruction[]
     */
    private $instructions = [];

    /**
     * @param Instruction $instruction
     *
     * @return self
     */
    public function instruction(Instruction $instruction): self
    {
        $this->instructions[] = $instruction;

        return $this;
    }

    /**
     * @param callable $handler
     *
     * @return callable
     */
    public function make(callable $handler): callable
    {
        foreach ($this->instructions as $instruction) {
            $handler = $instruction->process($handler);
        }

        return $handler;
    }
}
