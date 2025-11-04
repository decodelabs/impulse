<?php

/**
 * Impulse
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Impulse\Event;

/**
 * @phpstan-require-implements Stoppable
 */
trait StoppableTrait
{
    private bool $stopPropagation = false;

    public function stopPropagation(
        bool $flag = true
    ): void {
        $this->stopPropagation = $flag;
    }

    public function isPropagationStopped(): bool
    {
        return $this->stopPropagation;
    }
}
