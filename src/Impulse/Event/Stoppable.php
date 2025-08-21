<?php

/**
 * @package Impulse
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Impulse\Event;

use Psr\EventDispatcher\StoppableEventInterface as PsrStoppableEvent;

interface Stoppable extends PsrStoppableEvent
{
    public function stopPropagation(
        bool $flag = true
    ): void;
}
