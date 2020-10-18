<?php

namespace DTL\Invoke\Internal\ArgumentResolver;

use DTL\Invoke\Internal\ArgumentResolver;
use DTL\Invoke\Internal\Parameters;
use DTL\Invoke\Internal\ResolvedArguments;
use ReflectionFunctionAbstract;

class NamedArgumentResolver implements ArgumentResolver
{
    /**
     * {@inheritDoc}
     */
    public function resolve(Parameters $parameters, array $args): ResolvedArguments
    {
        $resolved = [];
        $unresolved = [];
        foreach ($args as $name => $value) {
            if (!$parameters->has($name)) {
                $unresolved[$name] = $value;
            }

            $resolved[$name] = $value;
        }

        return new ResolvedArguments($resolved, $unresolved);
    }
}
