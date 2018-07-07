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

use PHPinnacle\Identity\UUID;

class Packer
{
    /**
     * @var string
     */
    private $origin;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param string     $origin
     * @param Serializer $serializer
     */
    public function __construct(string $origin, Serializer $serializer)
    {
        $this->origin     = $origin;
        $this->serializer = $serializer;
    }

    /**
     * @param object $message
     * @param array  $headers
     *
     * @return Package
     */
    public function pack(object $message, array $headers = []): Package
    {
        $id   = (string) UUID::random();
        $body = $this->serializer->serialize($message);

        $headers[Package::HEADER_TIME]       = Time::seconds();
        $headers[Package::HEADER_SERIALIZER] = \get_class($this->serializer);

        return new Package($id, $this->origin, $body, $headers);
    }

    /**
     * @param Package $package
     *
     * @return object
     */
    public function unpack(Package $package): object
    {
        return $this->serializer->unserialize($package->body());
    }
}
