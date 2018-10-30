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
use Psr\Container\NotFoundExceptionInterface;

final class EmptyContainer implements ContainerInterface
{

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        throw new class extends \Exception implements NotFoundExceptionInterface {};
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return false;
    }
}
