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

class Send
{
    /**
     * @var string
     */
    private $destination;

    /**
     * @var object
     */
    private $message;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var array
     */
    private $headers;

    /**
     * @param string $destination
     * @param object $message
     * @param int    $timeout
     * @param array  $headers
     */
    public function __construct(string $destination, object $message, int $timeout = 0, array $headers = [])
    {
        $this->destination = $destination;
        $this->message     = $message;
        $this->timeout     = $timeout;
        $this->headers     = $headers;
    }

    /**
     * @return string
     */
    public function destination(): string
    {
        return $this->destination;
    }

    /**
     * @return object
     */
    public function message(): object
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function timeout(): int
    {
        return $this->timeout;
    }

    /**
     * @return array
     */
    public function headers(): array
    {
        return $this->headers;
    }
}
