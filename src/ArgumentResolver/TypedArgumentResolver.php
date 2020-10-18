<?php

namespace DTL\Invoke\ArgumentResolver;

use DTL\Invoke\ArgumentResolver;
use DTL\Invoke\Parameters;
use DTL\Invoke\ResolvedArguments;
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
