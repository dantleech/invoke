<?php

namespace DTL\Invoke;

use ReflectionFunctionAbstract;

interface ArgumentResolver
{
    /**
     * @return array<int,mixed>
     */
    public function resolve(Parameters $parameters, array $args): array;
}
