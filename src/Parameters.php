<?php

namespace DTL\Invoke;

use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;

class Parameters
{
    /**
     * @var array
     */
    private $parameterMap;

    /**
     * @param array<string,ReflectionParameter>
     */
    public function __construct(array $parameterMap)
    {
        $this->parameterMap = $parameterMap;
    }

    public static function fromRefelctionFunctionAbstract(ReflectionFunctionAbstract $function): self
    {
        return new self((array)array_combine(
            array_map(function (ReflectionParameter $function) {
                return $function->getName();
            }, $function->getParameters()),
            $function->getParameters()
        ));
    }

    public function required(): self
    {
        return new self(array_filter($this->parameterMap, function (ReflectionParameter $parameter) {
            return (bool) !$parameter->isDefaultValueAvailable();
        }));
    }

    /**
     * @return array<string>
     */
    public function keys(): array
    {
        return array_keys($this->parameterMap);
    }

    /**
     * @return array<string,ReflectionParameter>
     */
    public function toArray(): array
    {
        return $this->parameterMap;
    }

    public function defaults(): self
    {
        return new self(array_map(function (ReflectionParameter $parameter) {
            return $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
        }, $this->parameterMap));
    }
}
