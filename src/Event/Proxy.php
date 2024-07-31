<?php

/**
 * @package Impulse
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Impulse\Event;

/**
 * @template T of object
 */
interface Proxy
{
    /**
     * Get event type
     *
     * @return class-string<T>
     */
    public function getType(): string;

    /**
     * Get target object
     *
     * @return T
     */
    public function getTarget(): object;
}
