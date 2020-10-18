<?php

namespace DTL\Invoke\Tests;

use Closure;
use DTL\Invoke\Internal\Exception\ClassHasNoConstructor;
use DTL\Invoke\Internal\Exception\ReflectionError;
use DTL\Invoke\Invoke;
use DTL\Invoke\Internal\Exception\InvalidParameterType;
use DTL\Invoke\Internal\Exception\RequiredKeysMissing;
use DTL\Invoke\Internal\Exception\UnknownKeys;
use Generator;
use PHPUnit\Framework\TestCase;

class InvokeTest extends TestCase
{
    public function testNewWithNoConstructor()
    {
        $this->assertEquals(new TestClass1(), Invoke::new(TestClass1::class, []));
    }

    public function testExceptionIfNoConstructorAndKeys(): void
    {
        $this->expectException(ClassHasNoConstructor::class);
        $this->assertEquals(new TestClass1(), Invoke::new(TestClass1::class, [
            'one'
        ]));
    }

    public function testExceptionOnNonExistingClassOnInstantiate(): void
    {
        $this->expectException(ReflectionError::class);
        Invoke::new('THISISNOTEXISTING');
    }

    public function testExceptionIfClassDoesntHaveNamedMethod(): void
    {
        $this->expectException(ReflectionError::class);
        $class = new TestClass1();
        Invoke::method($class, 'THISISNOTEXISTING', []);
    }

    public function testInstantiateWithUnorderedArgs(): void
    {
        $this->assertEquals(
            new TestClass3('1', '2'),
            Invoke::new(TestClass3::class, [
                'two' => '2',
                'one' => '1',
            ])
        );
    }

    public function testWithConstructorWithArgument()
    {
        $this->assertEquals(
            new TestClass2('foobar'),
            Invoke::new(TestClass2::class, [
                'one' => 'foobar'
            ])
        );
    }

    public function testExceptionIfKeyIsNotSet()
    {
        $this->expectException(UnknownKeys::class);
        $this->assertEquals(
            new TestClass2('foobar'),
            Invoke::new(TestClass2::class, [
                'two' => 'foobar'
            ])
        );
    }

    public function testExceptionIfRequiredPropertyMissing()
    {
        $this->expectException(RequiredKeysMissing::class);
        $this->assertEquals(
            new TestClass2('foobar'),
            Invoke::new(TestClass2::class, [])
        );
    }

    public function testUsesDefaultValues()
    {
        $this->assertEquals(
            new TestClass3('foobar', 'barfoo'),
            Invoke::new(TestClass3::class, [
                'one' => 'foobar',
            ])
        );
    }

    /**
     * @dataProvider provideValidatesTypes
     */
    public function testValidatesTypes(array $params, string $expectedExceptionMessage = null)
    {
        if ($expectedExceptionMessage) {
            $this->expectException(InvalidParameterType::class);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $object = Invoke::new(TestClass4::class, $params);
        $this->assertInstanceOf(TestClass4::class, $object);
    }

    public function provideValidatesTypes()
    {
        yield 'no params' => [
            [],
            null
        ];

        yield 'string for array' => [
            [
                'array' => 'foobar',
            ],
            'Argument "array" has type "array" but was passed "string"'
        ];

        yield 'subclass of declared class' => [
            [
                'subclass' => new SubClassOfTestClass1(),
            ],
        ];

        yield 'declared class' => [
            [
                'subclass' => new TestClass1(),
            ],
        ];

        yield 'object type' => [
            [
                'object' => new TestClass1(),
            ],
        ];
    }

    public function testInvokeWithNamedParameters()
    {
        $subject = new TestClass5();
        Invoke::method($subject, 'callMe', [
            'string' => 'hello',
        ]);
        $this->assertEquals('hello', $subject->string);
    }

    public function testInvokeInvokeWithTypesOnly()
    {
        $subject = new TestClass6();
        Invoke::method($subject, 'callMe', [
            true,
            'hello',
            ['goodbye'],
            12,
            new TestClass1(),
        ], Invoke::MODE_TYPE);

        $this->assertEquals(true, $subject->bool);
        $this->assertEquals('hello', $subject->string);
        $this->assertEquals(['goodbye'], $subject->array);
        $this->assertEquals(12, $subject->int);
        $this->assertInstanceOf(TestClass1::class, $subject->class);
    }

    public function testInvokeExceptionOnMissingTypes()
    {
        $this->expectException(RequiredKeysMissing::class);
        $subject = new TestClass6();
        Invoke::method($subject, 'callMe', [
            'hello',
        ], Invoke::MODE_TYPE);

        $this->assertEquals(true, $subject->bool);
    }

    public function testParameterExceptionWhenArrayPassedToObject()
    {
        $this->expectException(InvalidParameterType::class);
        Invoke::new(ClassConstructor::class, [
            'class1' => [],
        ]);

        $this->assertEquals(true, $subject->bool);
    }
}

class TestClass1
{
}

class TestClass2
{
    /**
     * @var string
     */
    private $one;

    public function __construct(string $one)
    {
        $this->one = $one;
    }
}

class TestClass3
{
    /**
     * @var string
     */
    private $one;
    /**
     * @var string
     */
    private $two;

    public function __construct(string $one, string $two = 'barfoo')
    {
        $this->one = $one;
        $this->two = $two;
    }
}

class TestClass4
{
    /**
     * @var string
     */
    private $string;
    /**
     * @var array
     */
    private $array;
    /**
     * @var int
     */
    private $int;
    /**
     * @var bool
     */
    private $bool;

    public function __construct(string $string = '', array $array = [], int $int = 1, bool $bool = false, TestClass1 $subclass = null, object $object = null)
    {
        $this->string = $string;
        $this->array = $array;
        $this->int = $int;
        $this->bool = $bool;
    }
}

class TestClass5
{
    /**
     * @var string
     */
    public $string;

    public function callMe(string $string = '')
    {
        $this->string = $string;
    }
}

class TestClass6
{
    /**
     * @var string
     */
    public $string;
    /**
     * @var array
     */
    public $array;
    /**
     * @var int
     */
    public $int;
    /**
     * @var bool
     */
    public $bool;

    public $class;

    public function callMe(
        string $string,
        array $array,
        int $int,
        bool $bool,
        TestClass1 $class
    ) {
        $this->string = $string;
        $this->array = $array;
        $this->int = $int;
        $this->bool = $bool;
        $this->class = $class;
    }
}

class SubClassOfTestClass1 extends TestClass1
{
}

class ClassConstructor
{
    public function __construct(TestClass1 $class1)
    {
    }
}
