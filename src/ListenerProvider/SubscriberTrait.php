<?php

/**
 * @package Impulse
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Impulse\ListenerProvider;

use DecodeLabs\Impulse\Event\Proxy;
use DecodeLabs\Impulse\Event\WithAction;
use DecodeLabs\Impulse\Event\WithContext;
use DecodeLabs\Impulse\Subscription;
use ReflectionClass;

trait SubscriberTrait
{
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
                return $b->getPriority()->value <=> $a->getPriority()->value;
            });

            $this->sorted = true;
        }

        $type = $event instanceof Proxy ? $event->getType() : get_class($event);
        $action = $event instanceof WithAction ? $event->getAction() : null;
        $context = $event instanceof WithContext ? $event->getContext() : null;
        $types = $this->getEventTypes($type);

        foreach ($this->subscriptions as $subscription) {
            $subType = $subscription->getType();
            $subAction = $subscription->getAction();
            $subContext = $subscription->getContext();

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
                    $subAction !== null &&
                    $subAction !== $action
                )
            ) {
                continue;
            }

            /** @var Subscription<T> $subscription */
            yield $subscription;
        }
    }

    /**
     * Get event types
     *
     * @template T of object
     * @param class-string<T> $eventType
     * @return array<class-string>
     */
    protected function getEventTypes(
        string $eventType
    ): array {
        $ref = new ReflectionClass($eventType);
        $curr = $ref;

        do {
            $types[] = $curr->getName();
            $parent = $curr->getParentClass();
            $curr = $parent;
        } while ($curr);

        foreach ($ref->getInterfaceNames() as $interface) {
            $types[] = $interface;
        }

        return $types;
    }
}
