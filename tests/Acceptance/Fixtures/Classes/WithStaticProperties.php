<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Classes;

class WithStaticProperties
{
    private static $myInstance;

    private static $aString = 'my string in a static property';

    /**
     * @param mixed $myInstance
     */
    public static function setMyInstance($myInstance): void
    {
        self::$myInstance = $myInstance;
    }
}
