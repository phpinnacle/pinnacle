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

use PHPinnacle\Pinnacle\Contract\NoConfirmation;

final class Reject implements NoConfirmation
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $error;

    /**
     * @var string
     */
    private $text;

    /**
     * @var int
     */
    private $code;

    /**
     * @param string     $id
     * @param \Throwable $error
     */
    public function __construct(string $id, \Throwable $error)
    {
        $this->id    = $id;
        $this->error = \get_class($error);
        $this->text  = (string) $error->getMessage();
        $this->code  = (string) $error->getCode();
    }

    /**
     * @return string
     */
    public function error(): string
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function text(): string
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function code(): string
    {
        return $this->code;
    }
}
