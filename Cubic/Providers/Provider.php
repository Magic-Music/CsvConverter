<?php

namespace Cubic\Providers;

use Cubic\Container;

abstract class Provider
{
    abstract public function register(): void;

    public function bind(string $class, string $resolvesToClass): void
    {
        Container::bind($class, $resolvesToClass);
    }

    public function singleton(string $class, ?string $resolvesToClass = null): void
    {
        Container::singleton($class, $resolvesToClass);
    }

    public function singletons(array $singletons): void
    {
        foreach ($singletons as $class => $resolvesToClass) {
            if (is_numeric($class)) {
                $this->singleton($resolvesToClass);
            } else {
                $this->singleton($class, $resolvesToClass);
            }
        }
    }
}