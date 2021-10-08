<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Classes;

class WithStaticVariablesInsideMethods
{
    public function myInstanceMethod(): void
    {
        static $myVarInsideInstanceMethod = 'my value for static var in instance method';
    }

    public static function myStaticMethod(): void
    {
        static $myVarInsideStaticMethod = 'my value for static var in static method';
    }
}
