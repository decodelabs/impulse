# Impulse — Package Specification

> **Cluster:** `runtime`
> **Language:** `php`
> **Milestone:** `m4`
> **Repo:** `https://github.com/decodelabs/impulse`
> **Role:** Hook events

## Overview

### Purpose

Impulse provides a PSR-14 compatible event hook system for PHP applications. It enables event-driven architecture by allowing components to dispatch events and register listeners that respond to those events. The package extends the PSR-14 standard with additional features including:

- Hook-based listener registration using PHP attributes
- Context and action-based event filtering
- Priority-based listener ordering
- Single-use listeners
- Event propagation control
- Caching of hook maps for performance

Impulse is designed to be the central event system for Decode Labs applications, providing both global and local event scopes.

### Non-Goals

- Impulse does not provide event storage or persistence
- It does not handle asynchronous event processing
- It does not provide event serialization or transport mechanisms
- It does not implement event sourcing patterns

## Role in the Ecosystem

### Cluster & Positioning

Impulse belongs to the **runtime** cluster, providing core event dispatching capabilities that other packages can build upon. It sits alongside other runtime infrastructure packages like Kingdom (service container), Monarch (configuration), and Slingshot (dependency injection).

### Usage Contexts

Impulse is used throughout the Decode Labs ecosystem for:

- Framework-level event hooks (e.g., application lifecycle events)
- Plugin and extension systems
- Decoupled component communication
- Cross-cutting concerns (logging, caching, validation)
- Application-specific event handling

## Public Surface

### Key Types

- **`Impulse`** — Main service class implementing `EventDispatcherInterface` and `Service`. Provides the primary entry point for event dispatching and subscription management.

- **`Dispatcher`** — Core dispatcher class implementing PSR-14 `EventDispatcherInterface`. Handles event dispatch logic, listener invocation, and propagation control.

- **`Hook`** — Abstract base class for hook classes. Provides reflection-based subscription discovery from PHP attributes.

- **`Subscription`** — PHP attribute and class for defining event subscriptions. Supports type, context, action, priority, and single-use configuration.

- **`Priority`** — Enum defining listener priority levels: `Low`, `Medium`, `High`.

- **`ListenerProvider`** — Interface extending PSR-14 `ListenerProviderInterface`.

- **`ListenerProvider\Hook`** — Listener provider that discovers and indexes hook classes using Archetype. Supports caching via Atlas/Genesis.

- **`ListenerProvider\Compound`** — Listener provider that aggregates multiple providers.

- **`ListenerProvider\Subscribable`** — Interface extending `ListenerProvider` with subscription management methods.

- **`ListenerProvider\Subscriber`** — In-memory listener provider implementing `Subscribable`.

- **`Event\Emitted`** — Event wrapper that wraps any object with context and action metadata. Implements `Proxy`, `WithContext`, `WithAction`, and `Stoppable`.

- **`Event\Stoppable`** — Interface extending PSR-14 `StoppableEventInterface` with `stopPropagation()` method.

- **`Event\WithContext`** — Interface for events that have a context string.

- **`Event\WithAction`** — Interface for events that have an action string.

### Main Entry Points

- **`Impulse::provideService()`** — Service factory method for Kingdom integration. Creates an `Impulse` instance with a `CompoundListenerProvider` containing a `HookListenerProvider`.

- **`Impulse::dispatch()`** — Dispatches an event to all registered listeners.

- **`Impulse::emit()`** — Convenience method that wraps an object in an `Emitted` event and dispatches it.

- **`Impulse::subscribe()`** — Registers a subscription with the listener provider.

- **`Impulse::unsubscribe()`** — Removes a subscription from the listener provider.

- **`Subscribable::on()`** — Creates and registers a persistent subscription.

- **`Subscribable::once()`** — Creates and registers a single-use subscription.

## Dependencies

### Decode Labs

- **`archetype`** — Used by `HookListenerProvider` to scan for hook classes implementing the `Hook` interface.

- **`exceptional`** — Used for exception handling throughout the package.

- **`kingdom`** — Used for service container integration via `Service` interface.

- **`monarch`** — Used by `HookListenerProvider` to determine build ID for cache invalidation and to check development mode.

- **`slingshot`** — Used by `HookListenerProvider` to instantiate hook classes with dependency injection.

### External

- **`psr/event-dispatcher`** — PSR-14 interfaces for event dispatching and listener providers.

## Behaviour & Contracts

### Invariants

- All events must be objects (PSR-14 requirement)
- Listeners must accept an event object as their first parameter
- Subscription type must match listener parameter type (if specified)
- Listener priority determines execution order (higher priority first)
- Single-use listeners are automatically unsubscribed after first invocation
- Event propagation stops when a stoppable event calls `stopPropagation()`
- Dispatcher can be enabled/disabled globally via `enabled` property

### Input & Output Contracts

- **`dispatch(object $event): object`** — Accepts any object as an event, returns the same event after processing all listeners. Respects propagation stopping for stoppable events.

- **`emit(object $target, ?string $context, ?string $action): EmittedEvent`** — Wraps the target object in an `Emitted` event and dispatches it. Returns the emitted event.

- **`getListenersForEvent(object $event): iterable<callable>`** — Returns an iterable of listener callables for the given event, ordered by priority.

- **`subscribe(Subscription $subscription): void`** — Registers a subscription. Requires a subscribable listener provider.

- **`unsubscribe(Subscription $subscription): void`** — Removes a subscription. Requires a subscribable listener provider.

- **`on(?string $type, ?callable $listener, ?string $context, string|array|null $action, Priority $priority): Subscription`** — Creates and registers a persistent subscription.

- **`once(?string $type, ?callable $listener, ?string $context, string|array|null $action, Priority $priority): Subscription`** — Creates and registers a single-use subscription.

## Error Handling

Impulse uses the Exceptional pattern for error handling. Key exception types:

- **`InvalidArgument`** — Thrown when subscription listener does not accept an event object as its first parameter, or when listener type does not match subscription type.

Exceptions preserve the original service context and include detailed error messages.

## Configuration & Extensibility

### Extension Points

- **Custom Listener Providers** — Implement `ListenerProvider` or `Subscribable` to provide custom listener discovery and management.

- **Hook Classes** — Extend `Hook` and use `#[Subscription]` attributes on methods to register listeners declaratively.

- **Event Types** — Any object can be used as an event. Implement `Stoppable`, `WithContext`, or `WithAction` interfaces for additional functionality.

- **Compound Providers** — Combine multiple listener providers using `CompoundListenerProvider`.

### Configuration

- **Hook Map Caching** — When Atlas and Genesis are available, hook maps are cached to disk in `{localData}/impulse/hooks-{buildId}.php`. Cache is invalidated on build changes or in development mode.

- **Dispatcher Enable/Disable** — The dispatcher can be globally enabled or disabled via the `enabled` property, allowing event dispatching to be toggled without removing listeners.

## Interactions with Other Packages

- **Archetype** — Used to scan for hook classes.

- **Atlas** — Used for file operations when caching hook maps.

- **Monarch** — Used to determine build ID and development mode for cache management.

- **Slingshot** — Used to instantiate hook classes with dependency injection.

- **Kingdom** — Integrated as a service, allowing automatic resolution from the service container.

## Usage Examples

### Basic Event Dispatching

```php
use DecodeLabs\Impulse;

$impulse = new Impulse(
    new CompoundListenerProvider(
        new HookListenerProvider($archetype)
    )
);

// Dispatch a simple event
class UserCreated {
    public function __construct(
        public readonly string $userId
    ) {}
}

$event = new UserCreated('user-123');
$impulse->dispatch($event);
```

### Hook-Based Listeners

```php
use DecodeLabs\Impulse\Hook;
use DecodeLabs\Impulse\Subscription;
use DecodeLabs\Impulse\Priority;

class UserHooks extends Hook {
    #[Subscription(
        type: UserCreated::class,
        priority: Priority::High
    )]
    public function onUserCreated(UserCreated $event): void {
        // Handle user creation
    }

    #[Subscription(
        type: UserCreated::class,
        context: 'admin',
        action: 'import',
        priority: Priority::Low
    )]
    public function onAdminUserImport(UserCreated $event): void {
        // Handle admin import
    }
}
```

### Programmatic Subscriptions

```php
use DecodeLabs\Impulse\Priority;

// Persistent subscription
$subscription = $impulse->on(
    type: UserCreated::class,
    listener: fn(UserCreated $event) => $this->handleUserCreated($event),
    priority: Priority::High
);

// Single-use subscription
$subscription = $impulse->once(
    type: UserCreated::class,
    listener: fn(UserCreated $event) => $this->handleOnce($event)
);
```

### Emitted Events with Context

```php
// Emit an event with context and action
$user = new User('user-123');
$event = $impulse->emit(
    target: $user,
    context: 'admin',
    action: 'create'
);

// Listeners can filter by context and action
$subscription = $impulse->on(
    type: User::class,
    context: 'admin',
    action: 'create',
    listener: fn(Emitted $event) => $this->logAdminAction($event)
);
```

### Event Propagation Control

```php
use DecodeLabs\Impulse\Event\Stoppable;

class StoppableEvent implements Stoppable {
    use StoppableTrait;
    
    public function __construct(
        public readonly string $data
    ) {}
}

$event = new StoppableEvent('data');
$impulse->dispatch($event);

// In a listener:
$event->stopPropagation(); // Prevents further listeners from executing
```

## Implementation Notes (for Contributors)

### Architecture

- **PSR-14 Compliance** — The package strictly adheres to PSR-14 interfaces while extending functionality through additional interfaces and classes.

- **Hook Discovery** — `HookListenerProvider` uses Archetype to scan for hook classes, then uses reflection to discover `Subscription` attributes on methods. This index is cached when Atlas and Genesis are available.

- **Listener Matching** — Listeners are matched based on event type (class hierarchy), context (exact match or wildcard), and action (exact match or wildcard). Priority determines execution order.

- **Subscription Lifecycle** — Subscriptions can be persistent or single-use. Single-use subscriptions are automatically unsubscribed after first invocation during dispatch.

- **Event Wrapping** — The `emit()` method wraps objects in `Emitted` events, which provide context and action metadata. Listeners can choose to receive the wrapped event or the original target.

- **Provider Composition** — `CompoundListenerProvider` allows multiple providers to be combined, with the first subscribable provider handling subscription management.

### Performance Considerations

- Hook map caching significantly improves performance by avoiding repeated reflection and class scanning.
- Listener providers use priority queues for efficient listener ordering.
- Event matching uses indexed lookups for fast listener discovery.

### Design Decisions

- **Attribute-Based Hooks** — Using PHP attributes for hook registration provides a declarative, type-safe approach that integrates well with IDE tooling.

- **Context and Action Filtering** — These additional filtering dimensions allow for more granular event handling without requiring many event classes.

- **Emitted Event Wrapper** — Wrapping arbitrary objects in `Emitted` events provides metadata without requiring all events to implement specific interfaces.

- **Single-Use Listeners** — Automatic unsubscription after first use simplifies one-time event handling patterns.

## Testing & Quality

**Code Quality:** 4.5/5 — Mature, well-structured codebase with comprehensive type safety and PSR-14 compliance.

**README Quality:** 1/5 — Minimal documentation. Usage examples are not yet provided.

**Documentation:** 0/5 — No formal documentation beyond README.

**Tests:** 0/5 — No test suite currently.

See `composer.json` for supported PHP versions.

## Roadmap & Future Ideas

- Enhanced documentation and usage examples
- Test suite implementation
- Performance optimizations for large-scale event systems
- Additional event filtering capabilities
- Event middleware or transformation pipeline
- Integration examples with other Decode Labs packages

## References

- [PSR-14: Event Dispatcher](https://www.php-fig.org/psr/psr-14/)
- [Decode Labs Chorus](https://github.com/decodelabs/chorus)
- [Impulse Repository](https://github.com/decodelabs/impulse)

