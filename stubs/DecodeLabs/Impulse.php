<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */
namespace DecodeLabs;

use DecodeLabs\Veneer\Proxy as Proxy;
use DecodeLabs\Veneer\ProxyTrait as ProxyTrait;
use DecodeLabs\Impulse\Context as Inst;
use DecodeLabs\Impulse\Subscription as Ref0;
use Psr\EventDispatcher\ListenerProviderInterface as Ref1;
use DecodeLabs\Impulse\Event\Emitted as Ref2;
use DecodeLabs\Impulse\Priority as Ref3;

class Impulse implements Proxy
{
    use ProxyTrait;

    const VENEER = 'DecodeLabs\\Impulse';
    const VENEER_TARGET = Inst::class;

    public static Inst $instance;

    public static function subscribe(Ref0 $subscription): void {}
    public static function unsubscribe(Ref0 $subscription): void {}
    public static function getListenersForEvent(object $event): iterable {
        return static::$instance->getListenersForEvent(...func_get_args());
    }
    public static function getListenerProvider(): Ref1 {
        return static::$instance->getListenerProvider();
    }
    public static function dispatch(object $event): object {
        return static::$instance->dispatch(...func_get_args());
    }
    public static function emit(object $target, ?string $context = NULL, ?string $action = NULL): Ref2 {
        return static::$instance->emit(...func_get_args());
    }
    public static function setEnabled(bool $enabled): void {}
    public static function isEnabled(): bool {
        return static::$instance->isEnabled();
    }
    public static function on(?string $type = NULL, ?callable $listener = NULL, ?string $context = NULL, array|string|null $action = NULL, Ref3 $priority = DecodeLabs\Impulse\Priority::Medium): Ref0 {
        return static::$instance->on(...func_get_args());
    }
    public static function once(?string $type = NULL, ?callable $listener = NULL, ?string $context = NULL, array|string|null $action = NULL, Ref3 $priority = DecodeLabs\Impulse\Priority::Medium): Ref0 {
        return static::$instance->once(...func_get_args());
    }
    public static function createSubscription(?string $type = NULL, ?callable $listener = NULL, ?string $context = NULL, array|string|null $action = NULL, Ref3 $priority = DecodeLabs\Impulse\Priority::Medium, bool $singleUse = false): Ref0 {
        return static::$instance->createSubscription(...func_get_args());
    }
};
