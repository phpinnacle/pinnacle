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

namespace PHPinnacle\Pinnacle\Logger;

use Amp\ByteStream\ResourceOutputStream;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

final class ConsoleLogger extends AbstractLogger
{
    /**
     * @var LoggerInterface
     */
    private $inner;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->inner = new Logger($name, [$this->createHandler()]);
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        $this->inner->log($level, $message, $context);
    }

    /**
     * @return HandlerInterface
     */
    private function createHandler(): HandlerInterface
    {
        $handler = new StreamHandler(new ResourceOutputStream(\STDOUT));
        $handler->setFormatter(new ConsoleFormatter());
        $handler->pushProcessor(new PsrLogMessageProcessor());

        return $handler;
    }
}
