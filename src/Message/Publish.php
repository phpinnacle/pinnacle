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

final class Publish
{
    /**
     * @var object
     */
    private $message;

    /**
     * @var array
     */
    private $headers;

    /**
     * @param object $message
     * @param array  $headers
     */
    public function __construct(object $message, array $headers = [])
    {
        $this->message = $message;
        $this->headers = $headers;
    }

    /**
     * @return object
     */
    public function message(): object
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function channel(): string
    {
        return \get_class($this->message);
    }

    /**
     * @return array
     */
    public function headers(): array
    {
        return $this->headers;
    }
}
