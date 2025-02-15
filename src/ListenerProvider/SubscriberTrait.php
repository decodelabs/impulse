<?php

/**
 * @package Impulse
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Impulse\ListenerProvider;

use DecodeLabs\Impulse\Subscription;

trait SubscriberTrait
{
    use EventReflectionTrait;

    /**
     * @var array<Subscription<object>>
     */
    protected array $subscriptions = [];

    protected bool $sorted = false;

    /**
     * Get listeners for event
     *
     * @template T of object
     * @param T $event
     * @return iterable<Subscription<T>>
     */
    public function getListenersForEvent(
        object $event
    ): iterable {
        if (!$this->sorted) {
            usort($this->subscriptions, function ($a, $b) {
                return $b->priority->value <=> $a->priority->value;
            });

            $this->sorted = true;
        }

        $action = $this->getEventAction($event);
        $context = $this->getEventContext($event);
        $types = $this->getEventTypes($event);

        foreach ($this->subscriptions as $subscription) {
            $subType = $subscription->type;
            $subActions = $subscription->actions;
            $subContext = $subscription->context;

            if (
                (
                    $subType !== null &&
                    !in_array($subType, $types)
                ) ||
                (
                    $subContext !== null &&
                    $subContext !== $context
                ) ||
                (
                    $subActions !== null &&
                    !in_array($action, $subActions)
                )
            ) {
                continue;
            }

            /** @var Subscription<T> $subscription */
            yield $subscription;
        }
    }
}
