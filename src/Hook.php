<?php

/**
 * @package Impulse
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Impulse;

use Closure;
use ReflectionClass;
use ReflectionProperty;

abstract class Hook
{
    /**
     * Get list of subscriptions
     *
     * @internal
     * @return iterable<string,Subscription<object>>
     */
    public function getSubscriptions(): iterable
    {
        $ref = new ReflectionClass($this);
        $methods = $ref->getMethods(ReflectionProperty::IS_PUBLIC);

        foreach ($methods as $method) {
            $attributes = $method->getAttributes(Subscription::class);

            if (empty($attributes)) {
                continue;
            }

            $closure = $method->getClosure($this);

            foreach ($attributes as $attribute) {
                $args = $attribute->getArguments();
                $args['listener'] = $closure;

                yield $method->getName() => new Subscription(...$args);
            }
        }
    }

    /**
     * @internal
     */
    public function getListener(
        string $name
    ): Closure {
        $ref = new ReflectionClass($this);
        $method = $ref->getMethod($name);
        return $method->getClosure($this);
    }
}
