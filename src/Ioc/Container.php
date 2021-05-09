<?php

declare(strict_types = 1);

namespace Rush\Ioc;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Class Container
 * @package Rush\Ioc
 */
class Container
{
    /**
     * Container Instance
     * @var Container
     */
    protected static Container $instance;

    /**
     * Object Instances Pool
     * @var array
     */
    protected static array $pool = [];

    /**
     * Mapping Of Name To Class Full Name, Closure, And Alias
     * @var array
     */
    protected static array $well = [];

    /**
     * Container constructor
     * @access private
     */
    private function __construct() {}

    /**
     * Container cloner
     * @access private
     */
    private function __clone() {}

    /**
     * Set the container instance
     * @param Container $container An instance of Container.
     * @return void
     */
    public static function setInstance(Container $container): void
    {
        static::$instance = $container;
    }

    /**
     * Get the container instance
     * @return Container
     */
    public static function getInstance(): Container
    {
        ! isset(static::$instance) && (static::$instance = new static());

        return static::$instance;
    }

    /**
     * Make the instance and put it into pool
     * @param string $name Class name.
     * @param array $vars Parameters to initiate the class.
     * @param bool $isNew If make a new object when it exists.
     * @return object
     * @throws IocException
     */
    public function make(string $name, array $vars = [], bool $isNew = false): object
    {
        $name = $this->fetch($name);

        if (false === $isNew && isset(static::$pool[$name])) {
            return static::$pool[$name];
        }

        try {
            if (isset(static::$well[$name]) && static::$well[$name] instanceof Closure) {
                return static::$pool[$name] = $this->invokeFunc(static::$well[$name], $vars);
            } else {
                return static::$pool[$name] = $this->invokeClass($name, $vars);
            }
        } catch (ReflectionException $ex) {
            throw new IocException("Failed to make instance, " . $ex->getMessage());
        }
    }

    /**
     * Instance the closure by reflection
     * @param Closure $name Closure name.
     * @param array $vars Parameters to initiate the closure.
     * @return object
     * @throws IocException
     * @throws ReflectionException
     */
    protected function invokeFunc(Closure $name, array $vars): object
    {
        $reflectFunc = new ReflectionFunction($name);

        $this->parseAttribute($reflectFunc);

        $args = $this->parseArgs($reflectFunc, $vars);

        return $reflectFunc->invokeArgs($args);
    }

    /**
     * Instance the class by reflection
     * @param string $name Class name.
     * @param array $vars Parameters to initiate the class.
     * @return object
     * @throws IocException
     * @throws ReflectionException
     */
    protected function invokeClass(string $name, array $vars): object
    {
        $reflectClass = new ReflectionClass($name);

        $this->parseAttribute($reflectClass);

        $constructor = $reflectClass->getConstructor();
        if ($constructor instanceof ReflectionMethod && true === $constructor->isPublic()) {
            $args = $this->parseArgs($constructor, $vars);
            return $reflectClass->newInstanceArgs($args);
        }

        return $reflectClass->newInstanceWithoutConstructor();
    }

    /**
     * Parse params of the function or method
     * @param ReflectionFunctionAbstract $reflect Reflection object.
     * @param array $vars Parameters to initiate the closure.
     * @return array
     * @throws IocException
     * @throws ReflectionException
     */
    protected function parseArgs(ReflectionFunctionAbstract $reflect, array $vars): array
    {
        return array_map(
            fn($param) => $this->parseParameter($param, $vars),
            $reflect->getParameters()
        );
    }

    /**
     * Inject arguments by parameter name and type
     * @param ReflectionParameter $parameter Reflection object.
     * @param array $vars Arguments
     * @return mixed
     * @throws IocException
     * @throws ReflectionException
     */
    protected function parseParameter(ReflectionParameter $parameter, array $vars): mixed
    {
        $name = $parameter->getName();
        if (isset($vars[$name])) {
            return $vars[$name];
        }

        if ($parameter->hasType()) {
            $types = ltrim((string)$parameter->getType(), '?');
            $types = array_diff(explode('|', $types) ?: [], ['bool', 'int', 'float', 'string', 'array', 'mixed']);
            foreach ($types as $type) {
                if (class_exists($type)) {
                    return $this->get($type);
                }
            }
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new IocException("Failed to parse parameter, $name is missing.");
    }

    /**
     * Parse attribute
     * @param ReflectionClass|ReflectionFunctionAbstract $reflect Reflection object.
     */
    protected function parseAttribute(ReflectionClass|ReflectionFunctionAbstract $reflect)
    {
        if ($reflect instanceof ReflectionClass) {
            foreach ($reflect->getAttributes() as $attribute) {
                $attribute->newInstance();
            }

            foreach ($reflect->getMethods() as $method) {
                $this->parseAttribute($method);
            }
        } elseif ($reflect instanceof ReflectionFunctionAbstract) {
            foreach ($reflect->getAttributes() as $attribute) {
                $attribute->newInstance();
            }
        }
    }

    /**
     * Undocumented function
     * @param string $name Name of class, closure and alias.
     * @return bool
     */
    public function look(string $name): bool
    {
        return isset(static::$well[$name]);
    }

    /**
     * Fetch the final target in the well
     * @param string $name Name of class, closure and alias.
     * @return string
     */
    public function fetch(string $name): string
    {
        if (isset(static::$well[$name]) && is_string(static::$well[$name])) {
            return $this->fetch(static::$well[$name]);
        }

        return $name;
    }

    /**
     * Inject a target to the well.
     * @param string $name Name of class, closure and alias.
     * @param string|Closure $value Class name, closure, and alias.
     * @return void
     */
    public function inject(string $name, string|Closure $value): void
    {
        static::$well[$name] = $value;
    }

    /**
     * Determine whether exist an entry for the given identifier.
     * @param string $id Identifier of the entry to look for.
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset(static::$pool[$id]);
    }

    /**
     * Find an entry by its identifier.
     * @param string $id Identifier of the entry to look for.
     * @return object
     * @throws IocException
     */
    public function get(string $id): object
    {
        return static::$pool[$id] ?? $this->make($id);
    }

    /**
     * Set an entry and its identifier.
     * @param string $id Identifier of the entry.
     * @param object $value Target.
     * @return void
     */
    public function set(string $id, object $value): void
    {
        static::$pool[$id] = $value;
    }

    /**
     * Remove an entry by its identifier.
     * @param string $id Identifier of the entry.
     * @return void
     */
    public function del(string $id): void
    {
        unset(static::$pool[$id]);
    }

    /**
     * Clear all instances in pool
     * @return void
     */
    public function clear(): void
    {
        static::$pool = [];
    }
}
