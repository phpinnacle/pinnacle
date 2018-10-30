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

namespace PHPinnacle\Pinnacle\Container;

use Psr\Container\ContainerInterface;

final class ProxyContainer implements ContainerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $instances;

    /**
     * @param ContainerInterface $container
     * @param array              $instances
     */
    public function __construct(ContainerInterface $container, array $instances = [])
    {
        $instances[ContainerInterface::class] = $this;

        $this->container = $container;
        $this->instances = $instances;
    }

    /**
     * @param mixed  $id
     * @param object $instance
     *
     * @return self
     */
    public function add($id, object $instance = null): self
    {
        if (\is_object($id)) {
            $instance = $id;
            $id = \get_class($instance);
        }

        $this->instances[$id] = $instance;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $service = $this->instances[$id] ?? $this->container->get($id);

        return $service instanceof \Closure ? $service($this) : $service;
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return isset($this->instances[$id]) ? true : $this->container->has($id);
    }
}
