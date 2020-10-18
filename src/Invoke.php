<?php

namespace DTL\Invoke;

use DTL\Invoke\ArgumentResolver\NamedArgumentResolver;
use DTL\Invoke\ArgumentResolver\TypedArgumentResolver;
use DTL\Invoke\Exception\ClassHasNoConstructor;
use DTL\Invoke\Exception\InvalidParameterType;
use DTL\Invoke\Exception\RequiredKeysMissing;
use DTL\Invoke\Exception\UnknownKeys;
use ReflectionClass;
use ReflectionFunctionAbstract;
use ReflectionParameter;

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

    private function doCall(object $object, string $methodName, array $args)
    {
        $class = new ReflectionClass(get_class($object));
        $arguments = $this->resolveArguments($class, $methodName, $args);

        return $class->getMethod($methodName)->invoke($object, ...$arguments);
    }

    private function doInstantiate(string $className, array $args): object
    {
        $class = new ReflectionClass($className);

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


        $arguments = $this->resolveArguments($class, self::METHOD_CONSTRUCT, $args);

        return $class->newInstanceArgs($arguments);
    }

    private function resolveArguments(ReflectionClass $class, string $methodName, array $givenArgs): array
    {
        $method = $class->getMethod($methodName);
        $parameters = Parameters::fromRefelctionFunctionAbstract($method);

        $arguments = $this->mergeDefaults($parameters, $givenArgs);
        $arguments = $this->resolver->resolve($parameters, $arguments);

        $this->assertCorrectKeys($parameters, $givenArgs, $method);
        $this->assertRequiredKeys($parameters, $arguments, $method);

        return $arguments;
    }

    private function mergeDefaults(Parameters $parameters, array $givenArgs): array
    {
        return array_merge($parameters->defaults()->toArray(), $givenArgs);
    }

    private function assertCorrectKeys(Parameters $parameters, array $givenArgs, ReflectionFunctionAbstract $function): void
    {
        if (!$diff = array_diff(array_keys($givenArgs), $parameters->keys())) {
            return;
        }

        throw new UnknownKeys(sprintf(
            'Unknown keys "%s" for "%s", known keys: "%s"',
            implode('", "', $diff),
            $this->describe($function),
            implode('", "', $parameters->keys())
        ));
    }

    private function assertRequiredKeys(Parameters $parameters, array $givenArgs, ReflectionFunctionAbstract $function): void
    {
        if (!$diff = array_diff($parameters->required()->keys(), array_keys($givenArgs))) {
            return;
        }

        throw new RequiredKeysMissing(sprintf(
            'Required keys "%s" for "%s", are missing',
            implode('", "', $diff),
            $this->describe($function)
        ));
    }

    private function describe(ReflectionFunctionAbstract $function): string
    {
        return 'THIS IS NOT A DESCRIPTION';
    }
}
