<?php

/**
 * Impulse
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Impulse\Event;

/**
 * @template T of object
 * @implements Proxy<T>
 */
class Emitted implements
    Proxy,
    WithContext,
    WithAction,
    Stoppable
{
    use StoppableTrait;

    /**
     * @var T
     */
    public readonly object $target;
    public string $type { get => get_class($this->target); }
    public readonly ?string $context;
    public readonly ?string $action;

    /**
     * @param T $target
     */
    public function __construct(
        object $target,
        ?string $context,
        ?string $action
    ) {
        $this->target = $target;
        $this->context = $context;
        $this->action = $action;
    }
}
