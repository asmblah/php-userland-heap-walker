<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Functions;

use Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Classes\Thing;

function with_no_static_variables()
{
    return 21;
}

function with_two_static_variables(Thing $myNewInstance)
{
    static $aString = 'my string in a static variable', $myInstance;

    $myInstance = $myNewInstance;
}

function call_callable(callable $callable)
{
    return $callable(new Thing('an argument from a global function'));
}
