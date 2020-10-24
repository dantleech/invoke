<?php

namespace DTL\Invoke\Internal;

use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;
use RuntimeException;

class Parameters
{
    /**
     * @var array
     */
    private $parameterMap;

    /**
     * @var ReflectionFunctionAbstract
     */
    private $owner;

    /**
     * @param array<string,ReflectionParameter> $parameterMap
     */
    public function __construct(ReflectionFunctionAbstract $function, array $parameterMap)
    {
        $this->owner = $function;
        $this->parameterMap = $parameterMap;
    }

    public static function fromRefelctionFunctionAbstract(ReflectionFunctionAbstract $function): self
    {
        return new self($function, (array)array_combine(
            array_map(function (ReflectionParameter $function) {
                return $function->getName();
            }, $function->getParameters()),
            $function->getParameters()
        ));
    }

    public function required(): self
    {
        return new self($this->owner, array_filter($this->parameterMap, function (ReflectionParameter $parameter) {
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
        return new self($this->owner, array_map(function (ReflectionParameter $parameter) {
            return $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
        }, $this->parameterMap));
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->parameterMap);
    }

    public function get(string $key): ReflectionParameter
    {
        if (!$this->has($key)) {
            throw new RuntimeException(sprintf(
                'No parameter exists with key "%s"',
                $key
            ));
        }

        return $this->parameterMap[$key];
    }

    public function findOneByValueType($value): ?ReflectionParameter
    {
        foreach ($this->parameterMap as $name => $parameter) {
            $type = $parameter->getType();

            if (null === $type) {
                continue;
            }

            if (gettype($value) !== 'object' && $type->isBuiltin() && $type->getName() === $this->resolveInternalTypeName($value)) {
                return $parameter;
            }

            if (gettype($value) === 'object') {
                if (!is_a($value, $type->getName())) {
                    continue;
                }
                return $parameter;
            }
        }

        return null;
    }

    private function resolveInternalTypeName($value): string
    {
        $type = gettype($value);

        if ($type === 'integer') {
            return 'int';
        }

        if ($type === 'boolean') {
            return 'bool';
        }

        return $type;
    }

    public function describeOwner(): string
    {
        if ($this->owner instanceof ReflectionMethod) {
            return sprintf(
                '%s#%s',
                $this->owner->getDeclaringClass()->getName(),
                $this->owner->getName()
            );
        }

        return $this->owner->getName();
    }
}
