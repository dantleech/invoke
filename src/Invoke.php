<?php

namespace DTL\Invoke;

use DTL\Invoke\Internal\ArgumentResolver\NamedArgumentResolver;
use DTL\Invoke\Internal\ArgumentResolver\TypedArgumentResolver;
use DTL\Invoke\Internal\ArgumentsAssert;
use DTL\Invoke\Internal\Exception\ClassHasNoConstructor;
use DTL\Invoke\Internal\Exception\InvalidParameterType;
use DTL\Invoke\Internal\Exception\ReflectionError;
use DTL\Invoke\Internal\Exception\RequiredKeysMissing;
use DTL\Invoke\Internal\Exception\UnknownKeys;
use ReflectionClass;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;
use DTL\Invoke\Internal\ArgumentResolver;
use DTL\Invoke\Internal\Parameters;
use DTL\Invoke\Internal\ResolvedArguments;

class Invoke
{
    public const MODE_TYPE = 1;
    public const MODE_NAME = 2;

    /**
     * @var int
     */
    private $mode;

    /**
     * @var ArgumentResolver
     */
    private $resolver;

    private const METHOD_CONSTRUCT = '__construct';

    public function __construct(ArgumentResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public static function new(string $className, array $data = [], $mode = self::MODE_NAME)
    {
        return (new self(self::resolverFromMode($mode)))->doInstantiate($className, $data);
    }

    public static function method(object $object, string $methodName, array $args, int $mode = self::MODE_NAME)
    {
        return (new self(self::resolverFromMode($mode)))->doCall($object, $methodName, $args);
    }

    private static function resolverFromMode(int $mode): ArgumentResolver
    {
        if ($mode === self::MODE_TYPE) {
            return new TypedArgumentResolver();
        }

        return new NamedArgumentResolver();
    }

    private function doInstantiate(string $className, array $args): object
    {
        $class = $this->reflectClass($className);

        if (!$class->hasMethod(self::METHOD_CONSTRUCT)) {
            if (empty($args)) {
                return $class->newInstance();
            }

            throw new ClassHasNoConstructor(sprintf(
                'Class "%s" has no constructor, but was instantiated with keys "%s"',
                $class->getName(),
                implode('", "', array_keys($args))
            ));
        }

        return $class->newInstanceArgs(
            $this->resolveArguments($class, self::METHOD_CONSTRUCT, $args)
        );
    }

    private function doCall(object $object, string $methodName, array $args)
    {
        $class = $this->reflectClass(get_class($object));
        $arguments = $this->resolveArguments($class, $methodName, $args);

        return $class->getMethod($methodName)->invoke($object, ...$arguments);
    }

    private function resolveArguments(ReflectionClass $class, string $methodName, array $arguments): array
    {
        try {
            $method = $class->getMethod($methodName);
        } catch (ReflectionException $error) {
            throw new ReflectionError($error->getMessage(), 0, $error);
        }

        $parameters = Parameters::fromRefelctionFunctionAbstract($method);
        $resolved = $this->resolver->resolve($parameters, $arguments);

        ArgumentsAssert::noUnknownKeys($resolved, $parameters);
        ArgumentsAssert::requiredKeys($resolved, $parameters);
        ArgumentsAssert::types($resolved, $parameters);

        $arguments = $this->mergeDefaults($parameters, $resolved->resolved());

        return array_values($arguments);
    }

    private function mergeDefaults(Parameters $parameters, array $givenArgs): array
    {
        return array_merge($parameters->defaults()->toArray(), $givenArgs);
    }

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
