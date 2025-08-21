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

    public function subscribe(
        Subscription $subscription
    ): void {
        $this->subscriptions[$subscription->key] = $subscription;
        $this->sorted = false;
    }

    public function unsubscribe(
        Subscription $subscription
    ): void {
        unset($this->subscriptions[$subscription->key]);
    }
}
