<?php

namespace DTL\Invoke\ArgumentResolver;

use DTL\Invoke\ArgumentResolver;
use DTL\Invoke\Parameters;
use ReflectionFunctionAbstract;

class NamedArgumentResolver implements ArgumentResolver
{
    /**
     * {@inheritDoc}
     */
    public function resolve(Parameters $parameters, array $args): array
    {
        $resolved = [];
        foreach ($parameters->keys() as $key) {
            if (!isset($args[$key])) {
                continue;
            }
            $resolved[$key] = $args[$key];
        }

        return $resolved;
    }
}
