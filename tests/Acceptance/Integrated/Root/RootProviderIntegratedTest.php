<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Tests\Acceptance\Integrated\Root;

use Asmblah\HeapWalk\Root\GlobalRootProvider;
use Asmblah\HeapWalk\Tests\Acceptance\AcceptanceTestCase;
use Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Classes\Thing;
use Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Classes\WithStaticProperties;
use function Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Functions\with_two_static_variables;

/**
 * Class RootProviderIntegratedTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class RootProviderIntegratedTest extends AcceptanceTestCase
{
    /**
     * @var Thing
     */
    private $firstGlobal;
    /**
     * @var GlobalRootProvider
     */
    private $rootProvider;
    /**
     * @var Thing
     */
    private $secondGlobal;
    /**
     * @var Thing
     */
    private $staticPropertyValue;
    /**
     * @var Thing
     */
    private $staticVariableValue;

    public function setUp(): void
    {
        $this->firstGlobal = new Thing('my first one');
        $this->secondGlobal = new Thing('my second one');

        $this->staticVariableValue = new Thing('my static variable instance');
        with_two_static_variables($this->staticVariableValue);

        $this->staticPropertyValue = new Thing('my static property instance');
        WithStaticProperties::setMyInstance($this->staticPropertyValue);

        $this->rootProvider = new GlobalRootProvider(
            [
                'firstGlobal' => $this->firstGlobal,
                'secondGlobal' => $this->secondGlobal,
            ],
            [
                'Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Functions\with_no_static_variables',
                'Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Functions\with_two_static_variables',
            ],
            [
                WithStaticProperties::class,
            ]
        );
    }

    public function testGetRootsReturnsCorrectRoots(): void
    {
        $rootValues = $this->rootProvider->getRoots();

        static::assertCount(6, $rootValues);
        static::assertEquals(
            [
                'name' => 'firstGlobal',
                'value' => $this->firstGlobal,
            ],
            $rootValues[0]->toArray()
        );
        static::assertEquals(
            [
                'name' => 'secondGlobal',
                'value' => $this->secondGlobal,
            ],
            $rootValues[1]->toArray()
        );
        static::assertEquals(
            [
                'function' => 'Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Functions\with_two_static_variables',
                'name' => 'aString',
                'value' => 'my string in a static variable',
            ],
            $rootValues[2]->toArray()
        );
        static::assertEquals(
            [
                'function' => 'Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Functions\with_two_static_variables',
                'name' => 'myInstance',
                'value' => $this->staticVariableValue,
            ],
            $rootValues[3]->toArray()
        );
        static::assertEquals(
            [
                'class' => WithStaticProperties::class,
                'property' => 'myInstance',
                'value' => $this->staticPropertyValue,
            ],
            $rootValues[4]->toArray()
        );
        static::assertEquals(
            [
                'class' => WithStaticProperties::class,
                'property' => 'aString',
                'value' => 'my string in a static property',
            ],
            $rootValues[5]->toArray()
        );
    }
}
