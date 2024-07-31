<?php

/**
 * @package Impulse
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Impulse;

use DecodeLabs\Impulse;
use DecodeLabs\Impulse\ListenerProvider\Compound as CompoundListenerProvider;
use DecodeLabs\Impulse\ListenerProvider\Hook as HookListenerProvider;
use DecodeLabs\Impulse\ListenerProvider\Subscribable as SubscribableListenerProvider;
use DecodeLabs\Impulse\ListenerProvider\SubscribableTrait as SubscribableListenerProviderTrait;
use DecodeLabs\Veneer;
use Psr\EventDispatcher\ListenerProviderInterface as PsrListenerProvider;

/**
 * @extends Dispatcher<SubscribableListenerProvider>
 */
class Context extends Dispatcher implements SubscribableListenerProvider
{
    use SubscribableListenerProviderTrait;

    /**
     * Initialise listener provider
     */
    public function __construct(
        ?PsrListenerProvider $listenerProvider = null
    ) {
        if (
            $listenerProvider !== null &&
            !$listenerProvider instanceof SubscribableListenerProvider
        ) {
            $listenerProvider = new CompoundListenerProvider(
                $listenerProvider
            );
        }

        if ($listenerProvider === null) {
            $listenerProvider = new CompoundListenerProvider(
                //new HookListenerProvider(),
            );
        }

        $this->provider = $listenerProvider;
    }

    /**
     * Subscribe to event
     */
    public function subscribe(
        Subscription $subscription
    ): void {
        $this->provider->subscribe($subscription);
    }

    /**
     * Unsubscribe from event
     */
    public function unsubscribe(
        Subscription $subscription
    ): void {
        $this->provider->unsubscribe($subscription);
    }

    /**
     * Get listeners for event
     *
     * @template T of object
     * @param T $event
     * @return iterable<callable(T): void>
     */
    public function getListenersForEvent(
        object $event
    ): iterable {
        return $this->provider->getListenersForEvent($event);
    }
}

// Register the Veneer facade
Veneer::register(Context::class, Impulse::class);
