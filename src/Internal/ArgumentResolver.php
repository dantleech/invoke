<?php

namespace DTL\Invoke\Internal;

use ReflectionFunctionAbstract;

interface ArgumentResolver
{
    public function resolve(Parameters $parameters, array $args): ResolvedArguments;
}
