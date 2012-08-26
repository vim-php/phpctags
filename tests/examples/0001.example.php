<?php

define('GLOBAL_CONST_1', 'this is a global const.');

// Only available php 5.3+
const GLOBAL_CONST_2 = 'this is a global const too.';

$var1 = 1;

$var2 = 2;

// With a variable define in scope
function function_1()
{
    $var = 1;
    return $var;
}

// Without a variable define in scope
function function_2()
{
    return function_1();
}

trait Trait_1
{

}

interface Interface_1
{
    const CLASS_CONST = 'this is a const defined in this scope';

    function method_0();

    function method_1();

    function method_2();

    function method_3();
}

abstract class Class_1 implements Interface_1
{
    const CLASS_CONST = 'this is a const defined in this scope';

    public $var1;

    private $var2;

    protected $var3;

    function __construct()
    {
        $this->var1 = 1;
        $this->var2 = 2;
        $this->var3 = 3;
    }

    function method_0()
    {
        $var = 'this is a method with a variable defined in this scope';
        return $var;
    }

    public function method_1()
    {
        return 'this is a public method.';
    }

    private function method_2()
    {
        return 'this is a private method.';
    }

    protected function method_3()
    {
        return 'this is a protected method.';
    }
}

class Class_2 extends Class_1
{

}
