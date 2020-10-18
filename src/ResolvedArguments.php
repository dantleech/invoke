<?php

namespace DTL\Invoke;

class ResolvedArguments
{
    /**
     * @var array
     */
    private $resolved;

    /**
     * @var array
     */
    private $unresolved;

    public function __construct(array $resolved, array $unresolved)
    {
        $this->resolved = $resolved;
        $this->unresolved = $unresolved;
    }

    public function unresolved(): array
    {
        return $this->unresolved;
    }

    public function resolved(): array
    {
        return $this->resolved;
    }
}
