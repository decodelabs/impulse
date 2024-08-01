<?php

/**
 * @package Impulse
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Impulse\ListenerProvider;

use DecodeLabs\Impulse\ListenerProvider;
use DecodeLabs\Impulse\Subscription;
use Psr\EventDispatcher\ListenerProviderInterface as PsrListenerProvider;

class Compound implements
    ListenerProvider,
    Subscribable
{
    use SubscribableTrait;

    /**
     * @var array<PsrListenerProvider>
     */
    protected array $providers = [];

    public function __construct(
        PsrListenerProvider ...$providers
    ) {
        $this->providers = $providers;
    }

    /**
     * Subscribe to event
     */
    public function subscribe(
        Subscription $subscription
    ): void {
        $provider = $this->getSubscribableProvider();
        $provider->subscribe($subscription);
    }

    /**
     * Unsuscribe from event
     */
    public function unsubscribe(
        Subscription $subscription
    ): void {
        $provider = $this->getSubscribableProvider();
        $provider->unsubscribe($subscription);
    }

    /**
     * Get subscribable provider
     */
    protected function getSubscribableProvider(): Subscribable
    {
        foreach ($this->providers as $provider) {
            if ($provider instanceof Subscribable) {
                return $provider;
            }
        }

        return $this->providers[] = new Subscriber();
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
        foreach ($this->providers as $provider) {
            yield from $provider->getListenersForEvent($event);
        }
    }
}
