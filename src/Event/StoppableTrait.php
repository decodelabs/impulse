<?php

/**
 * @package Impulse
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Impulse\Event;

/**
 * @phpstan-require-implements Stoppable
 */
trait StoppableTrait
{
    protected bool $stopPropagation = false;

    /**
     * Stop event propagation
     */
    public function stopPropagation(
        bool $flag = true
    ): void {
        $this->stopPropagation = $flag;
    }

    /**
     * Is propagation stopped?
     */
    public function isPropagationStopped(): bool
    {
        return $this->stopPropagation;
    }
}
