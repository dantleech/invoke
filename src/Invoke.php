<?php

namespace DTL\Invoke;

use Closure;
use DTL\Invoke\Internal\ArgumentResolver\NamedArgumentResolver;
use DTL\Invoke\Internal\ArgumentAssert;
use DTL\Invoke\Exception\ClassHasNoConstructor;
use DTL\Invoke\Exception\ReflectionError;
use DTL\Invoke\Exception\InvokeException;
use ReflectionClass;
use ReflectionException;
use DTL\Invoke\Internal\ArgumentResolver;
use DTL\Invoke\Internal\Parameters;
use TypeError;

class Invoke
{
    /**
     * @var int
     */
    private $mode;

    /**
     * @var ArgumentResolver
     */
    private $resolver;

    private const METHOD_CONSTRUCT = '__construct';

    private function __construct()
    {
        $this->resolver = new NamedArgumentResolver();
    }

    /**
     * @template C of object
     * @param class-string<C> $className
     * @return C
     */
    public static function new(string $className, array $args = []): object
    {
        return (new self())->doInstantiate($className, $args);
    }

    /**
     * @return mixed
     */
    public static function method(object $object, string $methodName, array $args)
    {
        return (new self())->doCall($object, $methodName, $args);
    }

    /**
     * @template C of object
     * @param class-string<C> $className
     * @return C
     */
    private function doInstantiate(string $className, array $args): object
    {
        if (PHP_VERSION_ID >= 80000) {
            try {
                return new $className(...$args);
            } catch (\Error $error) {
            }
        }

        $class = $this->reflectClass($className);

        if (!$class->hasMethod(self::METHOD_CONSTRUCT)) {
            if (empty($args)) {
                $object = $class->newInstance();
                assert($object instanceof $className);
                return $object;
            }

            throw new ClassHasNoConstructor(sprintf(
                'Class "%s" has no constructor, but was instantiated with keys "%s"',
                $class->getName(),
                implode('", "', array_keys($args))
            ));
        }

        $object = $this->instantiate($class, self::METHOD_CONSTRUCT, $args, function (array $args) use ($class) {
            return $class->newInstanceArgs($args);
        });
        assert($object instanceof $className);
        return $object;
    }

    /**
     * @return mixed
     */
    private function doCall(object $object, string $methodName, array $args)
    {
        $class = $this->reflectClass(get_class($object));
        return $this->instantiate($class, $methodName, $args, function (array $arguments) use ($class, $object, $methodName) {
            return $class->getMethod($methodName)->invoke($object, ...$arguments);
        });
    }

    /**
     * @param ReflectionClass<object> $class
     * @return mixed
     */
    private function instantiate(
        ReflectionClass $class,
        string $methodName,
        array $arguments,
        Closure $factory
    ) {
        try {
            $method = $class->getMethod($methodName);
        } catch (ReflectionException $error) {
            throw new ReflectionError($error->getMessage(), 0, $error);
        }

        $parameters = Parameters::fromRefelctionFunctionAbstract($method);
        $resolved = $this->resolver->resolve($parameters, $arguments);
        $arguments = $this->mergeDefaults($parameters, $resolved->resolved());

        try {
            return $factory(array_values($arguments));
        } catch (TypeError $error) {
            ArgumentAssert::noUnknownKeys($resolved, $parameters);
            ArgumentAssert::requiredKeys($resolved, $parameters);
            ArgumentAssert::types($resolved, $parameters);

            throw new InvokeException(sprintf(
                'Unhandled type error when invoking "%s": %s',
                $class->getName(),
                $error->getMessage()
            ), 0, $error);
        }
    }

    private function mergeDefaults(Parameters $parameters, array $givenArgs): array
    {
        return array_merge($parameters->defaults()->toArray(), $givenArgs);
    }

    /**
     * @param class-string $className
     * @return ReflectionClass<object>
     */
    private function reflectClass(string $className): ReflectionClass
    {
        try {
            $class = new ReflectionClass($className);
        } catch (ReflectionException $error) {
            throw new ReflectionError($error->getMessage(), 0, $error);
        }
        return $class;
    }
}
