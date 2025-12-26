<?php

declare(strict_types=1);

namespace App\Core;

use ReflectionClass;
use ReflectionParameter;

class Container
{
    private array $bindings = [];
    private array $singletons = [];
    private array $instances = [];

    public function bind(string $abstract, callable|string $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, callable|string $concrete): void
    {
        $this->singletons[$abstract] = $concrete;
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function get(string $abstract): mixed
    {
        // Return existing instance if available
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Check if it's a singleton and already resolved
        if (isset($this->singletons[$abstract]) && isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $concrete = $this->bindings[$abstract] 
            ?? $this->singletons[$abstract] 
            ?? $abstract;

        $object = $this->resolve($concrete);

        // Store singleton instance
        if (isset($this->singletons[$abstract])) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    public function make(string $abstract): mixed
    {
        return $this->get($abstract);
    }

    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) 
            || isset($this->singletons[$abstract]) 
            || isset($this->instances[$abstract]);
    }

    private function resolve(callable|string $concrete): mixed
    {
        if (is_callable($concrete)) {
            return $concrete($this);
        }

        if (!class_exists($concrete)) {
            throw new \RuntimeException("Class {$concrete} does not exist");
        }

        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new \RuntimeException("Class {$concrete} is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $concrete();
        }

        $dependencies = $this->resolveDependencies($constructor->getParameters());

        return $reflector->newInstanceArgs($dependencies);
    }

    private function resolveDependencies(array $parameters): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $this->resolveParameter($parameter);
            $dependencies[] = $dependency;
        }

        return $dependencies;
    }

    private function resolveParameter(ReflectionParameter $parameter): mixed
    {
        $type = $parameter->getType();

        if ($type === null) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }
            throw new \RuntimeException(
                "Cannot resolve parameter {$parameter->getName()} without type hint"
            );
        }

        $typeName = $type->getName();

        if ($type->isBuiltin()) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }
            throw new \RuntimeException(
                "Cannot resolve built-in type {$typeName} for parameter {$parameter->getName()}"
            );
        }

        return $this->get($typeName);
    }
}
