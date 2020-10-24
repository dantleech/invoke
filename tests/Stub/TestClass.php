<?php

namespace DTL\Invoke\Tests\Stub;

class TestClass
{
    public function noTypeHint(
        $_
    )
    {
    }

    public function nullable(?string $_) {
    }

    public function bool(bool $_) {
    }

    public function int(int $_) {
    }

    public function float(float $_) {
    }

    public function array(array $_) {
    }

    public function object(object $_) {
    }

    public function stdClass(\stdClass $_) {
    }
}
