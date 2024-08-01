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
     * @var array<string,array<class-string<HookInterface>,array<string,array<string>>>>
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
            context: $this->getEventContext($event)
        );

        $eventAction = $this->getEventAction($event);

        $subscriptions = [];

        foreach ($keys as $key) {
            if (!isset($this->index[$key])) {
                continue;
            }

            foreach ($this->index[$key] as $class => $actions) {
                if (
                    $eventAction !== null &&
                    !isset($actions[$eventAction])
                ) {
                    continue;
                }

                $slingshot = new Slingshot();
                $hook = $slingshot->newInstance($class);
                $ref = new ReflectionClass($hook);
                $methodList = [];

                if ($eventAction === null) {
                    foreach ($actions as $action => $methods) {
                        $methodList = array_merge($methodList, $methods);
                    }
                } else {
                    $methodList = $actions[$eventAction] ?? [];
                }

                $methodList = array_unique($methodList);



                foreach ($methodList as $method) {
                    $methodRef = $ref->getMethod($method);
                    $attributes = $methodRef->getAttributes(Subscription::class);

                    foreach ($attributes as $attribute) {
                        $args = $attribute->getArguments();
                        $args['listener'] = $hook->getListener($method);
                        $subscription = new Subscription(...$args);

                        if (!$subscription->acceptsAction($eventAction)) {
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
        ?string $context
    ): array {
        $keys = [];
        array_unshift($types, '*');
        $contexts = ['*'];

        if ($context !== null) {
            $contexts[] = $context;
        }

        foreach ($types as $type) {
            foreach ($contexts as $context) {
                $keys[] = $type . ':' . $context;
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
     * @return array<string,array<class-string<HookInterface>,array<string,array<string>>>>
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
                $key = ($subscription->getType() ?? '*') . ':' . ($subscription->getContext() ?? '*');

                foreach ($subscription->getActions() ?? ['*'] as $action) {
                    $index[$key][get_class($hook)][$action][] = $name;
                }
            }
        }

        return $index;
    }
}
