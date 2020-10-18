<?php

namespace DTL\Invoke\Internal;

use DTL\Invoke\Internal\Exception\InvalidParameterType;
use DTL\Invoke\Internal\Exception\RequiredKeysMissing;
use DTL\Invoke\Internal\Exception\UnknownKeys;
use ReflectionClass;

class ArgumentsAssert
{
    public static function types(ResolvedArguments $resolved, Parameters $parameters): void
    {
        foreach ($resolved->resolved() as $key => $value) {
            if (!$parameters->has($key)) {
                continue;
            }

            $parameter = $parameters->get($key);
            $reflectionType = $parameter->getType();

            if (!$reflectionType) {
                continue;
            }

            if ($reflectionType->allowsNull() && is_null($value)) {
                continue;
            }

            $typeName = is_object($value) ? get_class($value) : gettype($value);

            if (!is_object($value)) {
                $typeName = ArgumentValue::resolveInternalTypeName($value);
            }

            if ($reflectionType->isBuiltin() && $reflectionType->getName() === $typeName) {
                continue;
            }

            if ($reflectionType->getName() === 'object' && is_object($value)) {
                continue;
            }

            if ($typeName !== 'array' && !$reflectionType->isBuiltin()) {
                $reflectionClass = new ReflectionClass($typeName);

                if ($typeName === $reflectionType->getName() || $reflectionClass->isSubclassOf($reflectionType->getName())) {
                    continue;
                }
            }

            throw new InvalidParameterType(sprintf(
                'Argument "%s" has type "%s" but was passed "%s" for "%s"',
                $parameter->getName(),
                $reflectionType->getName(),
                is_object($value) ? get_class($value) : gettype($value),
                $parameters->describeOwner()
            ));
        }
    }

    public static function noUnknownKeys(ResolvedArguments $resolved, Parameters $parameters): void
    {
        if (!$resolved->unresolved()) {
            return;
        }

        throw new UnknownKeys(sprintf(
            'Extra keys "%s" for "%s", known keys: "%s"',
            implode('", "', array_keys($resolved->unresolved())),
            $parameters->describeOwner(),
            implode('", "', $parameters->keys())
        ));
    }

    public static function requiredKeys(ResolvedArguments $resolved, Parameters $parameters): void
    {
        if (!$diff = array_diff($parameters->required()->keys(), array_keys($resolved->resolved()))) {
            return;
        }

        throw new RequiredKeysMissing(sprintf(
            'Required keys "%s" for "%s", are missing',
            implode('", "', $diff),
            $parameters->describeOwner()
        ));
    }
}
