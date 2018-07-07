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

class Event
{
    /**
     * @var object
     */
    private $message;

    /**
     * @param object $message
     */
    public function __construct(object $message)
    {
        $this->message = $message;
    }

    /**
     * @return object
     */
    public function message(): object
    {
        return $this->message;
    }
}
