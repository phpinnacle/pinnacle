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

class ResolutionException extends PinnacleException
{
    /**
     * @param string $id
     */
    public function __construct(string $id)
    {
        parent::__construct(sprintf('Message "%s" can\'t be resolved.', $id));
    }
}
