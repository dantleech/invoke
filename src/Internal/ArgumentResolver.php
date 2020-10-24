<?php

namespace DTL\Invoke\Internal;

interface ArgumentResolver
{
    public function resolve(Parameters $parameters, array $args): ResolvedArguments;
}
