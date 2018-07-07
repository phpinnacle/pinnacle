<?php
/**
 * This file is part of PHPinnacle/Ensign.
 *
 * (c) PHPinnacle Team <dev@phpinnacle.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace PHPinnacle\Pinnacle\Instruction;

use PHPinnacle\Pinnacle\Instruction;
use Psr\Log\LoggerInterface;

final class LoggerInstruction implements Instruction
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(callable $handler): callable
    {
        return function (...$arguments) use ($handler) {
            $this->logger->info('Message dispatched.');

            return $handler(...$arguments);
        };
    }
}
