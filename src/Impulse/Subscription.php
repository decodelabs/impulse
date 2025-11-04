<?php

/**
 * Impulse
 * @license https://opensource.org/licenses/MIT
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
    public protected(set) ?string $type = null;

    public protected(set) ?string $context = null;

    /**
     * @var array<string>|null
     */
    public protected(set) ?array $actions = null;

    public string $key {
        get =>
            ($this->type ?? '*') . ':' .
            ($this->context ?? '*') . '#' .
            (implode(',', $this->actions ?? ['*']));
    }

    public protected(set) Priority $priority = Priority::Medium;
    public protected(set) bool $singleUse = false;
    public protected(set) bool $emitted = false;

    /**
     * @var Closure(T|Emitted<T>): void
     */
    public protected(set) Closure $listener;

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
        ?Priority $priority = null,
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
                message: 'Subscription listener must accept an event object as its first argument'
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
                message: 'Subscription listener must accept an event object of type ' . $this->type
            );
        }
    }


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
            $event = $event->target;
        }

        /** @var T|Emitted<T> $event */
        ($this->listener)($event);
    }
}
