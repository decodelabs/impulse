<?php

/**
 * @package Impulse
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Impulse\Subscription;

use Closure;
use DecodeLabs\Impulse\Priority;
use DecodeLabs\Impulse\Subscription;

/**
 * @template T of object
 * @implements Subscription<T>
 */
class Generic implements Subscription
{
    /**
     * @var class-string<T>|null
     */
    protected ?string $type = null;

    protected ?string $context = null;
    protected ?string $action = null;
    protected Priority $priority = Priority::Medium;
    protected bool $singleUse = false;

    /**
     * @var Closure(T): void
     */
    protected Closure $listener;

    /**
     * @param class-string<T>|null $type
     * @param callable(T): void $listener
     */
    public function __construct(
        ?string $type,
        ?callable $listener = null,
        ?string $context = null,
        ?string $action = null,
        Priority $priority = null,
        bool $singleUse = false
    ) {
        $this->type = $type;
        $this->context = $context;
        $this->action = $action;
        $this->priority = $priority ?? Priority::Medium;
        $this->singleUse = $singleUse;
        $this->listener = Closure::fromCallable($listener ?? fn () => null);
    }


    /**
     * Get key
     */
    public function getKey(): string
    {
        return $this->type . ':' . $this->context . '#' . $this->action;
    }


    /**
     * Get event type
     *
     * @return class-string<T>|null
     */
    public function getType(): ?string
    {
        return $this->type;
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

    /**
     * Get priority
     */
    public function getPriority(): Priority
    {
        return $this->priority;
    }

    /**
     * Is single use
     */
    public function isSingleUse(): bool
    {
        return $this->singleUse;
    }

    /**
     * Get listener callback
     *
     * @return callable(T): void
     */
    public function getListener(): callable
    {
        return $this->listener;
    }

    /**
     * Invoke listener
     *
     * @param T $event
     */
    public function __invoke(
        object $event
    ): void {
        ($this->listener)($event);
    }
}
