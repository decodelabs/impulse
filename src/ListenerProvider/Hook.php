<?php

/**
 * @package Impulse
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Impulse\ListenerProvider;

use DecodeLabs\Archetype;
use DecodeLabs\Atlas;
use DecodeLabs\Genesis;
use DecodeLabs\Impulse\Hook as HookInterface;
use DecodeLabs\Impulse\ListenerProvider;
use DecodeLabs\Impulse\Subscription;
use DecodeLabs\Slingshot;
use Exception;
use ReflectionClass;
use Throwable;

class Hook implements ListenerProvider
{
    use EventReflectionTrait;

    /**
     * @var array<string,array<class-string<HookInterface>,array<string>>>
     */
    protected array $index = [];

    public function __construct()
    {
        $this->loadIndex();
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
        $keys = $this->createKeys(
            types: $this->getEventTypes($event),
            context: $this->getEventContext($event),
            action: $this->getEventAction($event)
        );

        $subscriptions = [];

        foreach ($keys as $key) {
            if (!isset($this->index[$key])) {
                continue;
            }

            foreach ($this->index[$key] as $class => $methods) {
                $slingshot = new Slingshot();
                $hook = $slingshot->newInstance($class);
                $ref = new ReflectionClass($hook);

                foreach ($methods as $method) {
                    $methodRef = $ref->getMethod($method);
                    $attributes = $methodRef->getAttributes(Subscription::class);

                    foreach ($attributes as $attribute) {
                        $args = $attribute->getArguments();
                        $args['listener'] = $hook->getListener($method);
                        $subscription = new Subscription(...$args);

                        if ($subscription->getKey() !== $key) {
                            continue;
                        }

                        $subscriptions[] = $subscription;
                    }
                }
            }
        }

        usort($subscriptions, function ($a, $b) {
            return $b->getPriority()->value <=> $a->getPriority()->value;
        });

        return $subscriptions;
    }

    /**
     * Create keys
     *
     * @param array<string> $types
     * @return array<string>
     */
    protected function createKeys(
        array $types,
        ?string $context,
        ?string $action
    ): array {
        $keys = [];
        array_unshift($types, '*');
        $contexts = $actions = ['*'];

        if ($context !== null) {
            $contexts[] = $context;
        }

        if ($action !== null) {
            $actions[] = $action;
        }

        foreach ($types as $type) {
            foreach ($contexts as $context) {
                foreach ($actions as $action) {
                    $keys[] = $type . ':' . $context . '#' . $action;
                }
            }
        }

        return $keys;
    }

    /**
     * Load and cache subscriptions from hooks
     */
    protected function loadIndex(): void
    {
        $buildId = null;

        try {
            $noCache =
                !class_exists(Atlas::class) ||
                !class_exists(Genesis::class) ||
                Genesis::$environment->isDevelopment() ||
                (null === ($buildId = Genesis::$build->getTime()));
        } catch (Throwable $e) {
            $noCache = true;
        }

        if ($noCache) {
            $this->index = $this->createIndex();
            return;
        }

        $dir = Atlas::dir(Genesis::$hub->getLocalDataPath() . '/impulse');
        $file = $dir->getFile('hooks-' . $buildId . '.php');

        if (!$file->exists()) {
            $this->index = $this->createIndex();
            $dir->emptyOut();
            $file->putContents('<?php return ' . var_export($this->index, true) . ';');
            return;
        }

        try {
            $index = include $file;

            if (!is_array($index)) {
                throw new Exception('Invalid index');
            }
        } catch (Throwable $e) {
            $this->index = $this->createIndex();
        }
    }

    /**
     * Create index
     *
     * @return array<string,array<class-string<HookInterface>,array<string>>>
     */
    protected function createIndex(): array
    {
        $index = [];

        foreach (Archetype::scanClasses(HookInterface::class) as $class) {
            $ref = new ReflectionClass($class);

            if (!$ref->isInstantiable()) {
                continue;
            }

            $slingshot = new Slingshot();
            $hook = $slingshot->newInstance($class);

            foreach ($hook->getSubscriptions() as $name => $subscription) {
                $index[$subscription->getKey()][get_class($hook)][] = $name;
            }
        }

        return $index;
    }
}
