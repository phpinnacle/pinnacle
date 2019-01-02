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

use Amp\Emitter;
use Amp\Iterator;
use Amp\Promise;

final class Channel implements Iterator
{
    /**
     * @var Iterator
     */
    private $consumer;

    /**
     * @var Emitter
     */
    private $finalizer;

    /**
     * @param Iterator $consumer
     * @param Emitter  $finalizer
     */
    public function __construct(Iterator $consumer, Emitter $finalizer)
    {
        $this->consumer  = $consumer;
        $this->finalizer = $finalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function advance(): Promise
    {
        return $this->consumer->advance();
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrent()
    {
        return $this->consumer->getCurrent();
    }
}
