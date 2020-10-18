<?php

namespace DTL\Invoke;

use ReflectionFunctionAbstract;

interface ArgumentResolver
{
    public function resolve(Parameters $parameters, array $args): ResolvedArguments;
}
