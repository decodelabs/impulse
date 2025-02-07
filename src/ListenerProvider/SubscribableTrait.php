<?php

/**
 * @package Impulse
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Impulse\ListenerProvider;

use DecodeLabs\Impulse\Event\Emitted;
use DecodeLabs\Impulse\Priority;
use DecodeLabs\Impulse\Subscription;

/**
 * @phpstan-require-implements Subscribable
 */
trait SubscribableTrait
{
    /**
     * Subscribe to event
     *
     * @template T of object
     * @param class-string<T>|null $type
     * @param ?callable(T|Emitted<T>): void $listener
     * @param string|array<string>|null $action
     * @return Subscription<T>
     */
    public function on(
        ?string $type = null,
        ?callable $listener = null,
        ?string $context = null,
        string|array|null $action = null,
        Priority $priority = Priority::Medium
    ): Subscription {
        $subscription = $this->createSubscription(
            type: $type,
            listener: $listener,
            context: $context,
            action: $action,
            priority: $priority,
            singleUse: false
        );

        $this->subscribe($subscription);
        return $subscription;
    }

    /**
     * Subscribe to event
     *
     * @template T of object
     * @param class-string<T>|null $type
     * @param ?callable(T|Emitted<T>): void $listener
     * @param string|array<string>|null $action
     * @return Subscription<T>
     */
    public function once(
        ?string $type = null,
        ?callable $listener = null,
        ?string $context = null,
        string|array|null $action = null,
        Priority $priority = Priority::Medium
    ): Subscription {
        $subscription = $this->createSubscription(
            type: $type,
            listener: $listener,
            context: $context,
            action: $action,
            priority: $priority,
            singleUse: true
        );

        $this->subscribe($subscription);
        return $subscription;
    }


    /**
     * Create a new subscription
     *
     * @template T of object
     * @param class-string<T>|null $type
     * @param ?callable(T|Emitted<T>): void $listener
     * @param string|array<string>|null $action
     * @return Subscription<T>
     */
    public function createSubscription(
        ?string $type = null,
        ?callable $listener = null,
        ?string $context = null,
        string|array|null $action = null,
        Priority $priority = Priority::Medium,
        bool $singleUse = false
    ): Subscription {
        return new Subscription(
            type: $type,
            context: $context,
            action: $action,
            priority: $priority,
            singleUse: $singleUse,
            listener: $listener
        );
    }
}
