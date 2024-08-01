<?php

/**
 * @package Impulse
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Impulse;

use Attribute;
use Closure;
use DecodeLabs\Exceptional;
use DecodeLabs\Impulse\Event\Emitted;
use ReflectionFunction;
use ReflectionNamedType;

/**
 * @template T of object
 */
#[Attribute(
    Attribute::TARGET_METHOD |
    Attribute::IS_REPEATABLE
)]
class Subscription
{
    /**
     * @var class-string<T>|null
     */
    protected ?string $type = null;

    protected ?string $context = null;

    /**
     * @var array<string>|null
     */
    protected ?array $actions = null;

    protected Priority $priority = Priority::Medium;
    protected bool $singleUse = false;
    protected bool $emitted = false;

    /**
     * @var Closure(T|Emitted<T>): void
     */
    protected Closure $listener;

    /**
     * @param class-string<T>|null $type
     * @param callable(T|Emitted<T>): void $listener
     * @param string|array<string>|null $action
     */
    public function __construct(
        ?string $type,
        ?callable $listener = null,
        ?string $context = null,
        string|array|null $action = null,
        Priority $priority = null,
        bool $singleUse = false,
        bool $emitted = false
    ) {
        if ($action !== null) {
            if (is_string($action)) {
                $action = [$action];
            }

            sort($action);

            if (empty($action)) {
                $action = null;
            }
        }

        $this->type = $type;
        $this->listener = Closure::fromCallable($listener ?? fn () => null);
        $this->context = $context;
        $this->actions = $action;
        $this->priority = $priority ?? Priority::Medium;
        $this->singleUse = $singleUse;
        $this->emitted = $emitted;

        if (!$listener) {
            return;
        }

        $ref = new ReflectionFunction($this->listener);
        $param = $ref->getParameters()[0] ?? null;

        if (!$param) {
            throw Exceptional::InvalidArgument(
                'Subscription listener must accept an event object as its first argument'
            );
        }

        if (
            !($listenerType = $param->getType()) ||
            !$listenerType instanceof ReflectionNamedType
        ) {
            return;
        }

        $this->emitted = $listenerType->getName() === Emitted::class;

        if (
            !$this->emitted &&
            $this->type !== null &&
            !is_a($this->type, $listenerType->getName(), true)
        ) {
            throw Exceptional::InvalidArgument(
                'Subscription listener must accept an event object of type ' . $this->type
            );
        }
    }


    /**
     * Get key
     */
    public function getKey(): string
    {
        return
            ($this->type ?? '*') . ':' .
            ($this->context ?? '*') . '#' .
            (implode(',', $this->actions ?? ['*']));
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
     *
     * @return array<string>|null
     */
    public function getActions(): ?array
    {
        return $this->actions;
    }

    /**
     * Accepts action
     */
    public function acceptsAction(
        ?string $action
    ): bool {
        if ($action === null) {
            return $this->actions === null;
        }

        return
            $this->actions === null ||
            in_array($action, $this->actions);
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
     * Is emitted
     */
    public function isEmitted(): bool
    {
        return $this->emitted;
    }

    /**
     * Get listener callback
     *
     * @return callable(T|Emitted<T>): void
     */
    public function getListener(): callable
    {
        return $this->listener;
    }

    /**
     * Invoke listener
     *
     * @param T|Emitted<T> $event
     */
    public function __invoke(
        object $event
    ): void {
        if (
            $this->emitted &&
            !$event instanceof Emitted
        ) {
            $event = new Emitted($event, $this->context, null);
        } elseif (
            !$this->emitted &&
            $event instanceof Emitted
        ) {
            $event = $event->getTarget();
        }

        /** @var T|Emitted<T> $event */
        ($this->listener)($event);
    }
}
