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

namespace PHPinnacle\Pinnacle\Exception;

use PHPinnacle\Pinnacle\Message;

final class RemoteException extends PinnacleException
{
    /**
     * @var string
     */
    private $remoteCode;

    /**
     * @var string
     */
    private $remoteError;

    /**
     * @param string $error
     * @param string $message
     * @param string $code
     */
    public function __construct(string $message, string $code, string $error)
    {
        parent::__construct($message, (int) $code);

        $this->remoteCode  = $code;
        $this->remoteError = $error;
    }

    /**
     * @param Message\Reject $command
     *
     * @return self
     */
    public static function reject(Message\Reject $command): self
    {
        return new self($command->text(), $command->code(), $command->error());
    }

    /**
     * @return string
     */
    public function code(): string
    {
        return $this->remoteCode;
    }

    /**
     * @return string
     */
    public function error(): string
    {
        return $this->remoteError;
    }
}
