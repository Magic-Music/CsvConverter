<?php

namespace Cubic;

class Container
{
    private static ?Container $container = null;

    private array $bindings = [];
    private array $singletons = [];
    private array $classes = [];

    public static function container(): Container
    {
        if (!self::$container) {
            self::$container = new Container();
        }

        return self::$container;
    }

    public static function getBindings(): array
    {
        return self::container()->bindings;
    }

    public static function getClass(string $class): object
    {
        $container = self::container();

        if (in_array($class, $container->singletons)) {
            return $container->classes[$class] ?? $container->getSingleton($class);
        }

        return $container->resolveClass($class);
    }

    public static function bind(string $className, string $implementationName): void
    {
        $container = self::container();

        $container->bindings[$className] = $implementationName;
    }

    public static function singleton(string $className, ?string $implementationName = null): void
    {
        $container = self::container();

        $container->singletons[] = $className;
        $container->bind($className, $implementationName ?? $className);
    }

    private function getSingleton(string $class): object
    {
        $this->classes[$class] = $this->resolveClass($class);

        return $this->classes[$class];
    }

    private function resolveClass($class): object
    {
        $object = ($this->bindings[$class] ?? $class);
        $constructorParams = (new \ReflectionClass($object))->getConstructor()?->getParameters();

        $params = [];
        foreach ($constructorParams ?? [] as $paramClass) {
            $paramClassName = $paramClass?->getType()?->getName();

            if ($paramClassName) {
                $params[] = self::getClass($paramClassName);
            }
        }

        return new $object(...$params);
    }
}