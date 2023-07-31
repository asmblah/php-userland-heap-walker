<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Traits;

trait WithStaticVariablesInsideMethodsTrait
{
    public function myInstanceMethod(): void
    {
        static $myVarInsideInstanceMethod = 'my value for static var in instance method of trait';
    }

    public static function myStaticMethod(): void
    {
        static $myVarInsideStaticMethod = 'my value for static var in static method of trait';
    }
}
