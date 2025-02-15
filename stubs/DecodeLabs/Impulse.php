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

    public const Veneer = 'DecodeLabs\\Impulse';
    public const VeneerTarget = Inst::class;

    protected static Inst $_veneerInstance;

    public static function subscribe(Ref0 $subscription): void {}
    public static function unsubscribe(Ref0 $subscription): void {}
    public static function getListenersForEvent(object $event): iterable {
        return static::$_veneerInstance->getListenersForEvent(...func_get_args());
    }
    public static function getListenerProvider(): Ref1 {
        return static::$_veneerInstance->getListenerProvider();
    }
    public static function dispatch(object $event): object {
        return static::$_veneerInstance->dispatch(...func_get_args());
    }
    public static function emit(object $target, ?string $context = NULL, ?string $action = NULL): Ref2 {
        return static::$_veneerInstance->emit(...func_get_args());
    }
    public static function setEnabled(bool $enabled): void {}
    public static function isEnabled(): bool {
        return static::$_veneerInstance->isEnabled();
    }
    public static function on(?string $type = NULL, ?callable $listener = NULL, ?string $context = NULL, array|string|null $action = NULL, Ref3 $priority = \DecodeLabs\Impulse\Priority::Medium): Ref0 {
        return static::$_veneerInstance->on(...func_get_args());
    }
    public static function once(?string $type = NULL, ?callable $listener = NULL, ?string $context = NULL, array|string|null $action = NULL, Ref3 $priority = \DecodeLabs\Impulse\Priority::Medium): Ref0 {
        return static::$_veneerInstance->once(...func_get_args());
    }
    public static function createSubscription(?string $type = NULL, ?callable $listener = NULL, ?string $context = NULL, array|string|null $action = NULL, Ref3 $priority = \DecodeLabs\Impulse\Priority::Medium, bool $singleUse = false): Ref0 {
        return static::$_veneerInstance->createSubscription(...func_get_args());
    }
};
