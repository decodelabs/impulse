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
    protected PsrListenerProvider $provider;

    /**
     * Initialise listener provider
     *
     * @param TProvider $provider
     */
    public function __construct(
        PsrListenerProvider $provider
    ) {
        $this->provider = $provider;
    }

    /**
     * Get listener provider
     */
    public function getListenerProvider(): PsrListenerProvider
    {
        return $this->provider;
    }

    /**
     * Dispatch event
     *
     * @template T of object
     * @param T $event
     * @return T
     */
    public function dispatch(
        object $event
    ): object {
        $listeners = $this->provider->getListenersForEvent($event);

        foreach ($listeners as $listener) {
            $listener($event);

            if (
                $listener instanceof Subscription &&
                $listener->isSingleUse() &&
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
     * Emit subscribable object
     *
     * @template T of object
     * @param T $target
     * @return EmittedEvent<T>
     */
    public function emit(
        object $target,
        ?string $context = null,
        ?string $action = null
    ): EmittedEvent {
        return $this->dispatch(
            new EmittedEvent(
                target: $target,
                context: $context,
                action: $action
            )
        );
    }
}
