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

namespace PHPinnacle\Pinnacle\Message;

final class Delay
{
    /**
     * @var string|int
     */
    private $interval;

    /**
     * @var object
     */
    private $message;

    /**
     * @param string|int $interval
     * @param object     $message
     */
    public function __construct($interval, object $message)
    {
        if (!\is_int($interval) and !\is_string($interval)) {
            throw new \InvalidArgumentException('Invalid interval. Expected int or string, got: ' . gettype($interval));
        }

        $this->interval = $interval;
        $this->message  = $message;
    }

    /**
     * @return \DateInterval
     */
    public function interval(): \DateInterval
    {
        $interval = $this->interval;

        if (\is_int($interval)) {
            $interval = "+{$interval} milliseconds";
        }

        return \DateInterval::createFromDateString($interval);
    }

    /**
     * @return object
     */
    public function message(): object
    {
        return $this->message;
    }
}
