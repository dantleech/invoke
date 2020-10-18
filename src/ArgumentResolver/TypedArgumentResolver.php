<?php

namespace DTL\Invoke\ArgumentResolver;

use DTL\Invoke\ArgumentResolver;
use ReflectionFunctionAbstract;

class TypedArgumentResolver implements ArgumentResolver
{
    /**
     * {@inheritDoc}
     */
    public function resolve(ReflectionFunctionAbstract $method, array $args): array
    {
        return $args;
    }
}
