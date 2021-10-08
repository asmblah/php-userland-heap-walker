<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Tests\Unit\Result;

use Asmblah\HeapWalk\Result\ClassTools;
use Asmblah\HeapWalk\Tests\TestCase;
use stdClass;

/**
 * Class ClassToolsTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ClassToolsTest extends TestCase
{
    public function testToReadableClassNameReturnsValidNormalClassUnchanged(): void
    {
        static::assertSame(stdClass::class, ClassTools::toReadableClassName(stdClass::class));
    }

    public function testToReadableClassNameReturnsNonExistentClassUnchanged(): void
    {
        static::assertSame('My\Stuff\MyClass', ClassTools::toReadableClassName('My\Stuff\MyClass'));
    }

    public function testToReadableClassNameReplacesAnonymousClassName(): void
    {
        $anonymousClassName = get_class(new class {});

        static::assertSame('\\__anonymous__', ClassTools::toReadableClassName($anonymousClassName));
    }
}
