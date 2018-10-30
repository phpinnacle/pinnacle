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

use Amp\Deferred;
use Amp\Loop;
use Amp\Promise;
use Amp\Success;

final class Synchronizer
{
    /**
     * @var Deferred[]
     */
    private $waiters = [];

    /**
     * {@inheritdoc}
     */
    public function wait(string $key, int $timeout = null): Promise
    {
        if ($timeout < 0) {
            return new Success();
        }

        $deferred = new Deferred();

        $this->waiters[$key] = &$deferred;

        if ($timeout > 0) {
            Loop::delay($timeout, function () use ($key) {
                if ($deferred = $this->waiters[$key] ?? null) {
                    $deferred->fail(new Exception\TimeoutException($key));
                }
            });
        }

        return $deferred->promise();
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $key): Promise
    {
        if (!$deferred = $this->waiters[$key] ?? null) {
            throw new Exception\ResolutionException($key);
        }

        $deferred->resolve();

        return $deferred->promise();
    }

    /**
     * {@inheritdoc}
     */
    public function reject(string $key, \Throwable $error): Promise
    {
        if (!$deferred = $this->waiters[$key] ?? null) {
            throw new Exception\ResolutionException($key);
        }

        unset($this->waiters[$key]);

        $deferred->fail($error);

        return $deferred->promise();
    }
}
