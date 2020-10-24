<?php

namespace DTL\Invoke\Tests\Benchmark;

use DTL\Invoke\Invoke;

/**
 * @Revs(1000)
 * @Iterations(10)
 * @OutputTimeUnit("microseconds")
 * @OutputTimeUnit("microseconds")
 */
class InvokeNewBench
{
    public function benchInvokeNewClass(): void
    {
        Invoke::new(TestClass::class, [
            'one' => 1,
            'two' => 2,
            'three' => 3,
            'four' => 4
        ]);
    }

    public function benchInstantiateNewClass(): void
    {
        new TestClass(1, 2, 3, 4);
    }
}

class TestClass
{
    public function __construct(int $one, int $two, int $three, int $four)
    {
    }
}
