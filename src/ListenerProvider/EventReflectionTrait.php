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
use ReflectionClass;

trait EventReflectionTrait
{
    /**
     * Get event types
     *
     * @template T of object
     * @param class-string<T> $eventType
     * @return array<class-string>
     */
    protected function getEventTypes(
        string|object $eventType
    ): array {
        if (is_object($eventType)) {
            $eventType = $eventType instanceof Proxy ?
                $eventType->getType() :
                get_class($eventType);
        }

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

    /**
     * Get event context
     *
     * @template T of object
     * @param T $event
     */
    protected function getEventContext(
        object $event
    ): ?string {
        return $event instanceof WithContext ? $event->getContext() : null;
    }

    /**
     * Get event action
     *
     * @template T of object
     * @param T $event
     */
    protected function getEventAction(
        object $event
    ): ?string {
        return $event instanceof WithAction ? $event->getAction() : null;
    }
}
