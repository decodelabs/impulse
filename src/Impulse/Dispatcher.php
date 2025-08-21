<?php

/**
 * @package Impulse
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Impulse;

use DecodeLabs\Impulse\Event\Emitted as EmittedEvent;
use DecodeLabs\Impulse\ListenerProvider\Subscribable;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcher;
use Psr\EventDispatcher\ListenerProviderInterface as PsrListenerProvider;
use Psr\EventDispatcher\StoppableEventInterface as PsrStoppableEvent;

/**
 * @template TProvider of PsrListenerProvider
 */
class Dispatcher implements PsrEventDispatcher
{
    /**
     * @var TProvider
     */
    public protected(set) PsrListenerProvider $provider;

    public bool $enabled = true;

    /**
     * @param TProvider $provider
     */
    public function __construct(
        PsrListenerProvider $provider
    ) {
        $this->provider = $provider;
    }

    public function getListenerProvider(): PsrListenerProvider
    {
        return $this->provider;
    }

    /**
     * @template T of object
     * @param T $event
     * @return T
     */
    public function dispatch(
        object $event
    ): object {
        if (!$this->enabled) {
            return $event;
        }

        /** @var iterable<callable(T):void> $listeners */
        $listeners = $this->provider->getListenersForEvent($event);

        foreach ($listeners as $listener) {
            $listener($event);

            if (
                $listener instanceof Subscription &&
                $listener->singleUse &&
                $this->provider instanceof Subscribable
            ) {
                $this->provider->unsubscribe($listener);
            }

            if (
                $event instanceof PsrStoppableEvent &&
                $event->isPropagationStopped()
            ) {
                break;
            }
        }

        return $event;
    }

    /**
     * @template T of object
     * @param T $target
     * @return EmittedEvent<T>
     */
    public function emit(
        object $target,
        ?string $context = null,
        ?string $action = null
    ): EmittedEvent {
        $event = new EmittedEvent(
            target: $target,
            context: $context,
            action: $action
        );

        if (!$this->enabled) {
            return $event;
        }

        return $this->dispatch($event);
    }


    public function setEnabled(
        bool $enabled
    ): void {
        $this->enabled = $enabled;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
