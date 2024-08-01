<?php

/**
 * @package Impulse
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Impulse\ListenerProvider;

use DecodeLabs\Impulse\ListenerProvider;
use DecodeLabs\Impulse\Priority;
use DecodeLabs\Impulse\Subscription;

interface Subscribable extends ListenerProvider
{
    /**
     * Subscribe to event
     *
     * @template T of object
     * @param class-string<T>|null $type
     * @param ?callable(T): void $listener
     * @param string|array<string>|null $action
     * @return Subscription<T>
     */
    public function on(
        ?string $type = null,
        ?callable $listener = null,
        ?string $context = null,
        string|array|null $action = null,
        Priority $priority = Priority::Medium
    ): Subscription;

    /**
     * Subscribe to event
     *
     * @template T of object
     * @param class-string<T>|null $type
     * @param ?callable(T): void $listener
     * @param string|array<string>|null $action
     * @return Subscription<T>
     */
    public function once(
        ?string $type = null,
        ?callable $listener = null,
        ?string $context = null,
        string|array|null $action = null,
        Priority $priority = Priority::Medium
    ): Subscription;

    /**
     * Subscribe to event
     *
     * @template T of object
     * @param Subscription<T> $subscription
     */
    public function subscribe(
        Subscription $subscription
    ): void;

    /**
     * Unsubscribe from event
     *
     * @template T of object
     * @param Subscription<T> $subscription
     */
    public function unsubscribe(
        Subscription $subscription
    ): void;

    /**
     * Create a new subscription
     *
     * @template T of object
     * @param class-string<T>|null $type
     * @param ?callable(T): void $listener
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
    ): Subscription;
}
