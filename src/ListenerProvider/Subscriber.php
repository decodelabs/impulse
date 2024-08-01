<?php

/**
 * @package Impulse
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Impulse\ListenerProvider;

use DecodeLabs\Impulse\ListenerProvider;
use DecodeLabs\Impulse\Subscription;

class Subscriber implements
    ListenerProvider,
    Subscribable
{
    use SubscribableTrait;
    use SubscriberTrait;

    /**
     * Subscribe to event
     */
    public function subscribe(
        Subscription $subscription
    ): void {
        $this->subscriptions[$subscription->getKey()] = $subscription;
        $this->sorted = false;
    }

    /**
     * Unsuscribe from event
     */
    public function unsubscribe(
        Subscription $subscription
    ): void {
        unset($this->subscriptions[$subscription->getKey()]);
    }
}
