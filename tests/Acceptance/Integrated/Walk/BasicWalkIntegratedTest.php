<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Tests\Acceptance\Integrated\Walk;

use Asmblah\HeapWalk\Result\Path\Descension\Root\GlobalVariableRoot;
use Asmblah\HeapWalk\Tests\Acceptance\AcceptanceTestCase;
use Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Classes\MagicSubThing;
use Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Classes\NormalSubThing;
use Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Classes\Thing;
use Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Classes\UncapturedClass;
use Asmblah\HeapWalk\Walker\ArrayWalker;
use Asmblah\HeapWalk\Walker\HeapWalker;
use Asmblah\HeapWalk\Walker\InstanceWalker;
use Asmblah\HeapWalk\Walker\ValueWalker;
use Closure;
use stdClass;

/**
 * Class BasicWalkIntegratedTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class BasicWalkIntegratedTest extends AcceptanceTestCase
{
    /**
     * @var HeapWalker
     */
    private $heapWalker;

    public function setUp(): void
    {
        $arrayWalker = new ArrayWalker();
        $instanceWalker = new InstanceWalker();
        $valueWalker = new ValueWalker($arrayWalker, $instanceWalker);
        $arrayWalker->setValueWalker($valueWalker);
        $instanceWalker->setValueWalker($valueWalker);

        $firstThing = new Thing('my first thing');
        $secondThing = new Thing('my second thing');

        $recursiveArray = [
            'one' => 1,
            'a-thing' => new Thing('my third thing'),
        ];
        $recursiveArray['self'] =& $recursiveArray;

        $recursiveThing = new Thing('my thing that contains a reference to itself via an array');
        $recursiveThing->setValue(['self' => $recursiveThing]);

        $thingForClosures = new Thing('my thing for closures');

        $thingInsideUncapturedObject = new UncapturedClass('I contain a thing');
        $thingInsideUncapturedObject->setValue(new Thing('I am inside another object'));

        $recursiveUncapturedObject = new UncapturedClass('I contain a reference to myself');
        $recursiveUncapturedObject->setValue($recursiveUncapturedObject);

        $subThingWithMagicGetter = new MagicSubThing('magic getter could shadow ->description');
        $subThingWithMagicGetter->setValue(new Thing('I am assigned to a shadowed private property'));

        $thingInsideDynamicProperty = new stdClass();
        $thingInsideDynamicProperty->myDynamicProp = new Thing('I am inside a dynamic property');

        $subThingWithParentPropertyUnset = new NormalSubThing('my ->description has been unset');
        $subThingWithParentPropertyUnset->unsetPrivateDescriptionProperty();

        $this->heapWalker = new HeapWalker($valueWalker, [
            // Non-Thing, to be ignored.
            new GlobalVariableRoot('stdClass-to-ignore', new stdClass()),
            // Array with some instances directly inside as elements.
            new GlobalVariableRoot('array-with-instance-elements', [
                'first' => $firstThing,
                'second' => $secondThing,
                'second-again' => $secondThing, // Same instance under a different key.
            ]),
            // An array that contains a recursive reference to itself.
            new GlobalVariableRoot('recursive-array', $recursiveArray),
            // An instance that contains an array with a reference back to the instance.
            new GlobalVariableRoot('recursive-instance', $recursiveThing),
            // A closure with $this set to an instance of Thing.
            new GlobalVariableRoot('closure-bound-to-thing', Closure::bind(function () {}, $thingForClosures)),
            // A closure that binds a variable containing an instance of Thing.
            // Note that it is static so that its $this does not point to the instance of this PHPUnit test class.
            new GlobalVariableRoot('closure-inheriting-thing', static function () use ($thingForClosures) {
                return $thingForClosures;
            }),
            // A Thing that is inside an instance of an uncaptured class.
            new GlobalVariableRoot('thing-inside-other-object', $thingInsideUncapturedObject),
            // An uncaptured non-Thing that just refers to itself, to test recursion handling.
            new GlobalVariableRoot('uncaptured-self-referencing-non-thing', $recursiveUncapturedObject),
            // A SubThing that contains a Thing in the private Thing->description property,
            // but that could be shadowed by SubThing::__get(...).
            new GlobalVariableRoot('sub-thing-with-magic-getter', $subThingWithMagicGetter),
            // Make sure dynamic properties are checked.
            new GlobalVariableRoot('thing-inside-dynamic-prop', $thingInsideDynamicProperty),
            // Make sure we can cope with declared properties being unset for an instance.
            new GlobalVariableRoot('sub-thing-with-parent-prop-unset', $subThingWithParentPropertyUnset),
        ]);
    }

    public function tearDown(): void
    {
        // Clear down data so that it does not affect subsequent tests,
        // as PHPUnit keeps test instances around until the end of the run.
        $this->heapWalker = null;
    }

    public function testWalkArrayWithInstances(): void
    {
        $pathSets = $this->heapWalker->getInstancePathSets([Thing::class]);

        static::assertCount(8, $pathSets);
        static::assertEquals(
            [
                'class' => Thing::class,
                'paths' => [
                    '$GLOBALS[array-with-instance-elements][first]',
                ],
            ],
            $pathSets[0]->toArray()
        );
        static::assertEquals(
            [
                'class' => Thing::class,
                'paths' => [
                    '$GLOBALS[array-with-instance-elements][second]',
                    '$GLOBALS[array-with-instance-elements][second-again]',
                ],
            ],
            $pathSets[1]->toArray()
        );
        static::assertEquals(
            [
                'class' => Thing::class,
                'paths' => [
                    '$GLOBALS[recursive-array][a-thing]',
                ],
            ],
            $pathSets[2]->toArray()
        );
        static::assertEquals(
            [
                'class' => Thing::class,
                'paths' => [
                    '$GLOBALS[recursive-instance]',
                    '$GLOBALS[recursive-instance]->(Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Classes\Thing::$value)[self]',
                ],
            ],
            $pathSets[3]->toArray()
        );
        static::assertEquals(
            [
                'class' => Thing::class,
                'paths' => [
                    '$GLOBALS[closure-bound-to-thing]->(Closure::$this)',
                    '$GLOBALS[closure-inheriting-thing] (Closure) use ($thingForClosures)',
                ],
            ],
            $pathSets[4]->toArray()
        );
        static::assertEquals(
            [
                'class' => Thing::class,
                'paths' => [
                    '$GLOBALS[thing-inside-other-object]->(Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Classes\UncapturedClass::$value)',
                ],
            ],
            $pathSets[5]->toArray()
        );
        static::assertEquals(
            [
                'class' => Thing::class,
                'paths' => [
                    '$GLOBALS[sub-thing-with-magic-getter]->(Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Classes\MagicSubThing::$value)',
                ],
            ],
            $pathSets[6]->toArray()
        );
        static::assertEquals(
            [
                'class' => Thing::class,
                'paths' => [
                    '$GLOBALS[thing-inside-dynamic-prop]->(stdClass::$myDynamicProp)',
                ],
            ],
            $pathSets[7]->toArray()
        );
    }
}
