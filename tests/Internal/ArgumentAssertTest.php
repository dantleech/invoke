<?php

namespace DTL\Invoke\Tests\Internal;

use DTL\Invoke\Internal\ArgumentAssert;
use DTL\Invoke\Internal\Exception\InvalidParameterType;
use DTL\Invoke\Internal\Parameters;
use DTL\Invoke\Internal\ResolvedArguments;
use DTL\Invoke\Tests\Stub\TestClass;
use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionParameter;

class ArgumentAssertTest extends TestCase
{
    /**
     * @dataProvider provideAssertTypes
     */
    public function testAssertTypes(ResolvedArguments $resolved, Parameters $parameters, bool $shouldThrow)
    {
        if ($shouldThrow) {
            $this->expectException(InvalidParameterType::class);
        } else {
            $this->addToAssertionCount(1);
        }

        ArgumentAssert::types($resolved, $parameters);
    }

    public function provideAssertTypes(): Generator
    {
        yield 'ignores non-existing' => [
            new ResolvedArguments([
                'nothere' => 1,
            ], []),
            $this->parameters('noTypeHint'),
            false
        ];

        yield 'ignores nullable nulls' => [
            new ResolvedArguments([
                '_' => null,
            ], []),
            $this->parameters('nullable'),
            false
        ];

        yield 'ignores correct internal type: bool' => [
            new ResolvedArguments([
                '_' => false,
            ], []),
            $this->parameters('bool'),
            false
        ];

        yield 'ignores correct internal type: int' => [
            new ResolvedArguments([
                '_' => 1,
            ], []),
            $this->parameters('int'),
            false
        ];

        yield 'ignores correct internal type: float' => [
            new ResolvedArguments([
                '_' => 1.2,
            ], []),
            $this->parameters('float'),
            false
        ];

        yield 'ignores correct internal type: array' => [
            new ResolvedArguments([
                '_' => [],
            ], []),
            $this->parameters('array'),
            false
        ];

        yield 'ignores correct internal type: object' => [
            new ResolvedArguments([
                '_' => new \stdClass(),
            ], []),
            $this->parameters('object'),
            false
        ];

        yield 'ignores correct class' => [
            new ResolvedArguments([
                '_' => new \stdClass(),
            ], []),
            $this->parameters('stdClass'),
            false
        ];

        yield 'exception on invalid type' => [
            new ResolvedArguments([
                '_' => new \stdClass(),
            ], []),
            $this->parameters('int'),
            true
        ];
    }

    private function parameters(string $method): Parameters
    {
        return Parameters::fromClassNameAndMethod(TestClass::class, $method);
    }
}
