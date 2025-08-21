<?php

/**
 * @package Impulse
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs;

use DecodeLabs\Impulse\Dispatcher;
use DecodeLabs\Impulse\ListenerProvider\Compound as CompoundListenerProvider;
use DecodeLabs\Impulse\ListenerProvider\Hook as HookListenerProvider;
use DecodeLabs\Impulse\ListenerProvider\Subscribable as SubscribableListenerProvider;
use DecodeLabs\Impulse\ListenerProvider\SubscribableTrait as SubscribableListenerProviderTrait;
use DecodeLabs\Impulse\Subscription;
use DecodeLabs\Kingdom\ContainerAdapter;
use DecodeLabs\Kingdom\Service;
use DecodeLabs\Kingdom\ServiceTrait;
use Psr\EventDispatcher\ListenerProviderInterface as PsrListenerProvider;

/**
 * @extends Dispatcher<SubscribableListenerProvider>
 */
class Impulse extends Dispatcher implements
    SubscribableListenerProvider,
    Service
{
    use SubscribableListenerProviderTrait;
    use ServiceTrait;

    public static function provideService(
        ContainerAdapter $container
    ): static {
        $archetype = $container->get(Archetype::class);

        // @phpstan-ignore-next-line
        return new self(new CompoundListenerProvider(
            new HookListenerProvider($archetype),
        ));
    }

    public function __construct(
        PsrListenerProvider $listenerProvider,
    ) {
        if (!$listenerProvider instanceof SubscribableListenerProvider) {
            $listenerProvider = new CompoundListenerProvider(
                $listenerProvider
            );
        }

        $this->provider = $listenerProvider;
    }

    public function subscribe(
        Subscription $subscription
    ): void {
        $this->provider->subscribe($subscription);
    }

    public function unsubscribe(
        Subscription $subscription
    ): void {
        $this->provider->unsubscribe($subscription);
    }

    /**
     * @template T of object
     * @param T $event
     * @return iterable<callable(T):void>
     */
    public function getListenersForEvent(
        object $event
    ): iterable {
        /** @var iterable<callable(T):void> $listeners */
        $listeners = $this->provider->getListenersForEvent($event);
        return $listeners;
    }
}
