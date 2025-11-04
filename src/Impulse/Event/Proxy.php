<?php

/**
 * Impulse
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Impulse\Event;

/**
 * @template T of object
 */
interface Proxy
{
    /**
     * @var class-string<T>
     */
    public string $type { get; }

    /**
     * @var T
     */
    public object $target { get; }
}
