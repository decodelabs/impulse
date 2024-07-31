<?php

/**
 * @package Impulse
 * @license http://opensource.org/licenses/MIT
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

    /**
     * Get target object
     *
     * @return T
     */
    public function getTarget(): object
    {
        return $this->target;
    }

    /**
     * Get event type
     */
    public function getType(): string
    {
        return get_class($this->target);
    }

    /**
     * Get context
     */
    public function getContext(): ?string
    {
        return $this->context;
    }

    /**
     * Get action
     */
    public function getAction(): ?string
    {
        return $this->action;
    }
}
