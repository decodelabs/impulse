<?php

/**
 * Impulse
 * @license https://opensource.org/licenses/MIT
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

    public function subscribe(
        Subscription $subscription
    ): void {
        $provider = $this->getSubscribableProvider();
        $provider->subscribe($subscription);
    }

    public function unsubscribe(
        Subscription $subscription
    ): void {
        $provider = $this->getSubscribableProvider();
        $provider->unsubscribe($subscription);
    }

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
     * @template T of object
     * @param T $event
     * @return iterable<callable(T):void>
     */
    public function getListenersForEvent(
        object $event
    ): iterable {
        foreach ($this->providers as $provider) {
            /** @var iterable<callable(T):void> $listeners */
            $listeners = $provider->getListenersForEvent($event);
            yield from $listeners;
        }
    }
}
