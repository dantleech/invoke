<?php

namespace DTL\Invoke\Tests\Internal;

use DTL\Invoke\Internal\ArgumentAssert;
use DTL\Invoke\Exception\InvalidParameterType;
use DTL\Invoke\Exception\RequiredKeysMissing;
use DTL\Invoke\Exception\UnknownKeys;
use DTL\Invoke\Internal\Parameters;
use DTL\Invoke\Internal\ResolvedArguments;
use DTL\Invoke\Tests\Stub\TestClass;
use Generator;
use PHPUnit\Framework\TestCase;

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

        yield 'type: resource' => [
            new ResolvedArguments([
                '_' => STDOUT,
            ], []),
            $this->parameters('resource'),
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

        yield 'invalid type: resource' => [
            new ResolvedArguments([
                '_' => STDOUT,
            ], []),
            $this->parameters('int'),
            true
        ];
    }

    /**
     * @dataProvider provideUnknownKeys
     */
    public function testUnknownKeys(ResolvedArguments $resolved, Parameters $parameters, bool $shouldThrow): void
    {
        if ($shouldThrow) {
            $this->expectException(UnknownKeys::class);
        } else {
            $this->addToAssertionCount(1);
        }

        ArgumentAssert::noUnknownKeys($resolved, $parameters);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideUnknownKeys(): Generator
    {
        yield 'no unresolved' => [
            new ResolvedArguments([
                '_' => 1,
            ], []),
            $this->parameters('int'),
            false
        ];

        yield 'unresolved' => [
            new ResolvedArguments([
                '_' => 1,
            ], [
                'asd' => 1,
            ]),
            $this->parameters('int'),
            true
        ];
    }

    /**
     * @dataProvider provideRequiredKeys
     */
    public function testRequiredKeys(ResolvedArguments $resolved, Parameters $parameters, bool $shouldThrow): void
    {
        if ($shouldThrow) {
            $this->expectException(RequiredKeysMissing::class);
        } else {
            $this->addToAssertionCount(1);
        }

        ArgumentAssert::requiredKeys($resolved, $parameters);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideRequiredKeys(): Generator
    {
        yield 'no missing required keys' => [
            new ResolvedArguments([
                '_' => 1,
            ], []),
            $this->parameters('int'),
            false
        ];

        yield 'missing required key' => [
            new ResolvedArguments([], []),
            $this->parameters('int'),
            true
        ];
    }

    private function parameters(string $method): Parameters
    {
        return Parameters::fromClassNameAndMethod(TestClass::class, $method);
    }
}
