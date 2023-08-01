<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Tests\Acceptance\Functional\Walk;

use Asmblah\HeapWalk\HeapWalk;
use Asmblah\HeapWalk\Result\Path\PathSetInterface;
use Asmblah\HeapWalk\Tests\Acceptance\AcceptanceTestCase;
use Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Classes\Thing;
use function Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Functions\call_callable;

/**
 * Class BasicWalkFunctionalTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class BasicWalkFunctionalTest extends AcceptanceTestCase
{
    /**
     * @var HeapWalk
     */
    private $heapWalk;
    /**
     * @var Thing
     */
    private static $thing;

    public function setUp(): void
    {
        self::$thing = new Thing('a property to find of this PHPUnit test instance');

        $this->heapWalk = new HeapWalk();
    }

    public function tearDown(): void
    {
        // Clear down data so that it does not affect subsequent tests,
        // as PHPUnit keeps test instances around until the end of the run.
        $this->heapWalk = null;
        self::$thing = null;
    }

    /** @noinspection PhpUnusedPrivateFieldInspection */
    public function testGetInstancePathSetsFetchesRealHeapValues(): void
    {
        // Add various types of frame onto the call stack to check for handling.
        $finalClosure = function () {
            return $this->heapWalk->getInstancePathSets([Thing::class]);
        };
        $staticClosure = static function ($param) use ($finalClosure) {
            return $finalClosure();
        };
        $normalBoundClosure = (function ($param) use ($staticClosure) {
            return $staticClosure(new Thing('an argument to a static closure'));
        })->bindTo((object) [
            'someProp' => new Thing('a property of a closure\'s $this object'),
        ]);
        $normalUnboundClosure = (function ($param) use ($normalBoundClosure) {
            return $normalBoundClosure(new Thing('an argument to a normal bound closure'));
        })->bindTo(null);
        $closureInStaticAnonymousClassScope = (function () use ($normalUnboundClosure) {
            /** @noinspection PhpUndefinedFieldInspection */
            static::$myStaticProperty = new Thing('a static property of isolated anonymous class');

            return $normalUnboundClosure(new Thing('an argument to a normal unbound closure'));
        })->bindTo(null, get_class(new class {
            private static $myStaticProperty; // Will be set above.
        }));

        /** @var PathSetInterface[] $pathSets */
        $pathSets = call_callable($closureInStaticAnonymousClassScope);

        static::assertCount(7, $pathSets);
        static::assertEquals(
            [
                'class' => Thing::class,
                'paths' => [
                    __CLASS__ . '::' . __NAMESPACE__ . '\{closure}(<arg>)',
                ],
            ],
            $pathSets[0]->toArray()
        );
        static::assertInstanceOf(Thing::class, $pathSets[0]->getEventualValue());
        static::assertSame('an argument to a static closure', $pathSets[0]->getEventualValue()->getDescription());
        static::assertEquals(
            [
                'class' => Thing::class,
                'paths' => [
                    __CLASS__ . '->' . __NAMESPACE__ . '\{closure}(<arg>)',
                ],
            ],
            $pathSets[1]->toArray()
        );
        static::assertInstanceOf(Thing::class, $pathSets[1]->getEventualValue());
        static::assertSame('an argument to a normal bound closure', $pathSets[1]->getEventualValue()->getDescription());
        static::assertEquals(
            [
                'class' => Thing::class,
                'paths' => [
                    __CLASS__ . '::' . __NAMESPACE__ . '\{closure}(<arg>)',
                ],
            ],
            $pathSets[2]->toArray()
        );
        static::assertInstanceOf(Thing::class, $pathSets[2]->getEventualValue());
        static::assertSame('an argument to a normal unbound closure', $pathSets[2]->getEventualValue()->getDescription());
        static::assertEquals(
            [
                'class' => Thing::class,
                'paths' => [
                    '\__anonymous__::' . __NAMESPACE__ . '\{closure}(<arg>)',
                ],
            ],
            $pathSets[3]->toArray()
        );
        static::assertInstanceOf(Thing::class, $pathSets[3]->getEventualValue());
        static::assertSame('an argument from a global function', $pathSets[3]->getEventualValue()->getDescription());
        static::assertEquals(
            [
                'class' => Thing::class,
                'paths' => [
                    'Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Functions\call_callable(<arg>) (Closure) use ($normalUnboundClosure) (Closure) use ($normalBoundClosure)->(Closure::$this)->(stdClass::$someProp)',
                ],
            ],
            $pathSets[4]->toArray()
        );
        static::assertInstanceOf(Thing::class, $pathSets[4]->getEventualValue());
        static::assertSame('a property of a closure\'s $this object', $pathSets[4]->getEventualValue()->getDescription());
        static::assertEquals(
            [
                'class' => Thing::class,
                'paths' => [
                    '\__anonymous__::$myStaticProperty',
                ],
            ],
            $pathSets[5]->toArray()
        );
        static::assertInstanceOf(Thing::class, $pathSets[5]->getEventualValue());
        static::assertSame('a static property of isolated anonymous class', $pathSets[5]->getEventualValue()->getDescription());
        static::assertEquals(
            [
                'class' => Thing::class,
                'paths' => [
                    __CLASS__ . '::$thing',
                ],
            ],
            $pathSets[6]->toArray()
        );
        static::assertInstanceOf(Thing::class, $pathSets[6]->getEventualValue());
        static::assertSame('a property to find of this PHPUnit test instance', $pathSets[6]->getEventualValue()->getDescription());
    }
}
