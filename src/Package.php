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

class Package
{
    const
        HEADER_SERIALIZER = 'serializer',
        HEADER_IDENTITY   = 'identity',
        HEADER_TIME       = 'time'
    ;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $origin;

    /**
     * @var string
     */
    private $body;

    /**
     * @var array
     */
    private $headers;

    /**
     * @param string $id
     * @param string $origin
     * @param string $body
     * @param array  $headers
     */
    public function __construct(string $id, string $origin, string $body, array $headers = [])
    {
        $this->id      = $id;
        $this->origin  = $origin;
        $this->body    = $body;
        $this->headers = $headers;
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
     * @return string
     */
    public function body(): string
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function headers(): array
    {
        return $this->headers;
    }
}
