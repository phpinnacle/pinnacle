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

namespace PHPinnacle\Pinnacle\Context;

use PHPinnacle\Pinnacle\Context;
use PHPinnacle\Pinnacle\Package;

final class RemoteContext implements Context
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $origin;

    /**
     * @var array
     */
    private $headers;

    /**
     * @param string $id
     * @param string $origin
     * @param array  $headers
     */
    public function __construct(string $id, string $origin, array $headers = [])
    {
        $this->id      = $id;
        $this->origin  = $origin;
        $this->headers = $headers;
    }

    /**
     * @param Package $package
     *
     * @return self
     */
    public static function create(Package $package): self
    {
        return new self($package->id(), $package->origin(), $package->headers());
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
    public function origin(): string
    {
        return $this->origin;
    }

    /**
     * @return array
     */
    public function headers(): array
    {
        return $this->headers;
    }
}
