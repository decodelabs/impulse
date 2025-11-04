<?php

/**
 * Impulse
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Impulse\ListenerProvider;

use DecodeLabs\Archetype;
use DecodeLabs\Atlas;
use DecodeLabs\Impulse\Hook as HookInterface;
use DecodeLabs\Impulse\ListenerProvider;
use DecodeLabs\Monarch;
use DecodeLabs\Slingshot;
use Exception;
use ReflectionClass;
use SplPriorityQueue;
use Throwable;

/**
 * @phpstan-type Index = array<string,array<class-string<HookInterface>,array<string,array<string,int>>>>
 */
class Hook implements ListenerProvider
{
    use EventReflectionTrait;

    /**
     * @var Index
     */
    protected array $index = [];

    public function __construct(
        protected Archetype $archetype
    ) {
        $this->loadIndex();
    }

    /**
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

        $eventAction = $this->getEventAction($event) ?? '*';
        /** @var SplPriorityQueue<int,callable(T):void> */
        $listeners = new SplPriorityQueue();

        foreach ($keys as $key) {
            if (!isset($this->index[$key])) {
                continue;
            }

            foreach ($this->index[$key] as $class => $actions) {
                if (!isset($actions[$eventAction])) {
                    continue;
                }

                $slingshot = new Slingshot();
                $hook = $slingshot->newInstance($class);
                $ref = new ReflectionClass($hook);

                foreach ($actions[$eventAction] as $method => $priority) {
                    $methodRef = $ref->getMethod($method);
                    $listeners->insert($methodRef->getClosure($hook), $priority);
                }
            }
        }

        return $listeners;
    }

    /**
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

    protected function loadIndex(): void
    {
        $buildId = null;

        try {
            $noCache =
                !class_exists(Atlas::class) ||
                Monarch::isDevelopment() ||
                (null === ($buildId = Monarch::getBuild()->time));
        } catch (Throwable $e) {
            $noCache = true;
        }

        if ($noCache) {
            $this->index = $this->createIndex();
            return;
        }

        $dir = Atlas::getDir(Monarch::getPaths()->localData . '/impulse');
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

            /** @var Index $index */
            $this->index = $index;
        } catch (Throwable $e) {
            $this->index = $this->createIndex();
        }
    }

    /**
     * @return array<string,array<class-string<HookInterface>,array<string,array<string,int>>>>
     */
    protected function createIndex(): array
    {
        $index = [];

        foreach ($this->archetype->scanClasses(HookInterface::class) as $class) {
            $ref = new ReflectionClass($class);

            if (!$ref->isInstantiable()) {
                continue;
            }

            $slingshot = new Slingshot();
            $hook = $slingshot->newInstance($class);

            foreach ($hook->getSubscriptions() as $name => $subscription) {
                $key = ($subscription->type ?? '*') . ':' . ($subscription->context ?? '*');

                foreach ($subscription->actions ?? ['*'] as $action) {
                    $index[$key][get_class($hook)][$action][$name] = $subscription->priority->value;
                }
            }
        }

        return $index;
    }
}
