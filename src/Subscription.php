<?php

/**
 * @package Impulse
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Impulse;

/**
 * @template T of object
 */
interface Subscription
{
    public function getKey(): string;

    /**
     * Get event type
     *
     * @return class-string<T>|null
     */
    public function getType(): ?string;

    public function getContext(): ?string;
    public function getAction(): ?string;
    public function getPriority(): Priority;
    public function isSingleUse(): bool;

    /**
     * Get listener callback
     *
     * @return callable(T): void
     */
    public function getListener(): callable;

    /**
     * Invoke listener
     *
     * @param T $event
     */
    public function __invoke(
        object $event
    ): void;
}
