<?php

namespace DTL\Invoke\Internal\ArgumentResolver;

use DTL\Invoke\Internal\ArgumentResolver;
use DTL\Invoke\Internal\Parameters;
use DTL\Invoke\Internal\ResolvedArguments;
use ReflectionFunctionAbstract;

class TypedArgumentResolver implements ArgumentResolver
{
    /**
     * {@inheritDoc}
     */
    public function resolve(Parameters $parameters, array $arguments): ResolvedArguments
    {
        $resolved = [];
        $unresolved = [];

        foreach ($arguments as $name => $value) {
            if ($parameter = $parameters->findOneByValueType($value)) {
                $resolved[$parameter->getName()] = $value;
                continue;
            }
            $unresolved[$name] = $value;
        }

        return new ResolvedArguments($resolved, $unresolved);
    }
}
